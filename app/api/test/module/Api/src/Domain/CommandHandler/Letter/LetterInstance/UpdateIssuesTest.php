<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Letter\LetterInstance;

use Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterInstance\UpdateIssues as CommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\LetterInstance as LetterInstanceRepo;
use Dvsa\Olcs\Api\Domain\Repository\LetterIssue as LetterIssueRepo;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance as LetterInstanceEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceTodo;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueTodo;
use Dvsa\Olcs\Api\Entity\Letter\LetterTodoVersion;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssue as LetterIssueEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion as LetterIssueVersionEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Letter\IssueChange\IssueChangePlanner;
use Dvsa\Olcs\Api\Service\Letter\LetterInstanceComposer;
use Dvsa\Olcs\Api\Service\Letter\LetterInstanceGrabSnapshotter;
use Dvsa\Olcs\Transfer\Command\Letter\LetterInstance\UpdateIssues as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Change the issues on a letter that has already been generated (VOL-7734)
 */
class UpdateIssuesTest extends AbstractCommandHandlerTestCase
{
    private const LETTER_ID = 55;

    private m\MockInterface $mockSnapshotter;

    private ?LetterInstanceTodo $deletedTodo = null;

    /** @var array<int, LetterIssueEntity> */
    private array $issues = [];

    public function setUp(): void
    {
        $this->mockSnapshotter = m::mock(LetterInstanceGrabSnapshotter::class);

        $this->mockedSmServices = [
            LetterInstanceComposer::class => new LetterInstanceComposer(),
            IssueChangePlanner::class => new IssueChangePlanner(),
            LetterInstanceGrabSnapshotter::class => $this->mockSnapshotter,
        ];

        $this->sut = new CommandHandler();
        $this->mockRepo('LetterInstance', LetterInstanceRepo::class);
        $this->mockRepo('LetterIssue', LetterIssueRepo::class);

        parent::setUp();

        $this->repoMap['LetterIssue']->shouldReceive('fetchById')
            ->andReturnUsing(fn(int $id) => $this->issues[$id]);
    }

    private function issue(int $id, ?string $goodsOrPsv = null, array $todoVersions = []): LetterIssueEntity
    {
        $issue = m::mock(LetterIssueEntity::class)->makePartial();
        $issue->setId($id);

        $issueTodos = array_map(function (LetterTodoVersion $todoVersion) {
            $issueTodo = m::mock(LetterIssueTodo::class)->makePartial();
            $issueTodo->shouldReceive('getLetterTodoVersion')->andReturn($todoVersion);
            return $issueTodo;
        }, $todoVersions);

        $version = m::mock(LetterIssueVersionEntity::class)->makePartial();
        $version->shouldReceive('getLetterIssueTodos')->andReturn($issueTodos);
        $version->setLetterIssue($issue);
        $version->setHeading('Issue ' . $id);
        if ($goodsOrPsv !== null) {
            $version->setGoodsOrPsv(new RefData($goodsOrPsv));
        }
        $issue->setCurrentVersion($version);

        return $this->issues[$id] = $issue;
    }

    private function letter(string $status = LetterInstanceEntity::STATUS_DRAFT, ?string $goodsOrPsv = null): LetterInstanceEntity
    {
        $letterInstance = new LetterInstanceEntity();
        $letterInstance->setId(self::LETTER_ID);
        $letterInstance->setStatus(new RefData($status));

        if ($goodsOrPsv !== null) {
            $licence = m::mock(LicenceEntity::class)->makePartial();
            $licence->setGoodsOrPsv(new RefData($goodsOrPsv));
            $licence->shouldReceive('isNi')->andReturn(false);
            $letterInstance->setLicence($licence);
        }

        $this->repoMap['LetterInstance']->shouldReceive('fetchUsingId')->andReturn($letterInstance);

        return $letterInstance;
    }

    private function onLetter(LetterInstanceEntity $letterInstance, LetterIssueEntity $issue, int $order): LetterInstanceIssue
    {
        $row = new LetterInstanceIssue();
        $row->setLetterIssueVersion($issue->getCurrentVersion());
        $row->setDisplayOrder($order);
        $letterInstance->addLetterInstanceIssue($row);

        return $row;
    }

    /**
     * @return list<int>
     */
    private function parentIds(LetterInstanceEntity $letterInstance): array
    {
        $rows = $letterInstance->getLetterInstanceIssues()->toArray();
        usort($rows, static fn($a, $b) => $a->getDisplayOrder() <=> $b->getDisplayOrder());

        return array_map(static fn($r) => $r->getLetterIssueVersion()->getLetterIssue()->getId(), $rows);
    }

    private function expectSaveAssigningIds(): void
    {
        $this->repoMap['LetterInstance']->shouldReceive('save')->once()->andReturnUsing(
            function (LetterInstanceEntity $letterInstance): void {
                $nextId = 900;
                foreach ($letterInstance->getLetterInstanceIssues() as $row) {
                    if ($row->getId() === null) {
                        $row->setId($nextId++);
                    }
                }
                foreach ($letterInstance->getLetterInstanceTodos() as $todo) {
                    if ($todo->getId() === null) {
                        $todo->setId($nextId++);
                    }
                }
                // as Doctrine does: a deleted row loses its id on flush
                $this->deletedTodo?->setId(null);
            }
        );
    }

    public function testAddsAndRemovesIssuesAndSaves(): void
    {
        $letterInstance = $this->letter();
        $this->onLetter($letterInstance, $this->issue(1), 0);
        $two = $this->issue(2);

        $this->mockSnapshotter->shouldReceive('snapshotRows')->once()->with(
            $letterInstance,
            m::on(fn(array $rows) => count($rows) === 1 && $rows[0]->getLetterIssueVersion() === $two->getCurrentVersion()),
            []
        );
        $this->expectSaveAssigningIds();

        $result = $this->sut->handleCommand(Cmd::create(['id' => self::LETTER_ID, 'selectedIssues' => [2]]));

        $this->assertSame([2], $this->parentIds($letterInstance));
        $this->assertSame([900], $result->getFlag('addedIssueIds'));
        $this->assertSame(['Issues updated: 1 added, 1 removed'], $result->getMessages());
    }

    public function testRefusesWhenEditsWouldBeLostWithoutConfirmation(): void
    {
        $letterInstance = $this->letter();
        $this->onLetter($letterInstance, $this->issue(1), 0)->setEditedContent(['blocks' => []]);

        $this->repoMap['LetterInstance']->shouldNotReceive('save');
        $this->mockSnapshotter->shouldNotReceive('snapshotRows');

        $result = $this->sut->handleCommand(Cmd::create(['id' => self::LETTER_ID, 'selectedIssues' => []]));

        $this->assertTrue($result->getFlag('confirmationRequired'));
        $this->assertSame(['Issue 1'], $result->getMessages());
        $this->assertSame([1], $this->parentIds($letterInstance), 'nothing may change before confirmation');
    }

    public function testAppliesWhenLossConfirmed(): void
    {
        $letterInstance = $this->letter();
        $this->onLetter($letterInstance, $this->issue(1), 0)->setEditedContent(['blocks' => []]);

        $this->mockSnapshotter->shouldReceive('snapshotRows')->once();
        $this->expectSaveAssigningIds();

        $result = $this->sut->handleCommand(
            Cmd::create(['id' => self::LETTER_ID, 'selectedIssues' => [], 'confirmLoss' => true])
        );

        $this->assertNull($result->getFlag('confirmationRequired'));
        $this->assertSame([], $this->parentIds($letterInstance));
    }

    public function testNoChangesSavesNothing(): void
    {
        $letterInstance = $this->letter();
        $this->onLetter($letterInstance, $this->issue(1), 0);

        $this->repoMap['LetterInstance']->shouldNotReceive('save');
        $this->mockSnapshotter->shouldNotReceive('snapshotRows');

        $result = $this->sut->handleCommand(Cmd::create(['id' => self::LETTER_ID, 'selectedIssues' => [1]]));

        $this->assertSame(['No changes to issues'], $result->getMessages());
    }

    public function testRepeatedIssueIdIsOnlyAddedOnce(): void
    {
        $letterInstance = $this->letter();
        $this->issue(3);

        $this->mockSnapshotter->shouldReceive('snapshotRows')->once();
        $this->expectSaveAssigningIds();

        $this->sut->handleCommand(Cmd::create(['id' => self::LETTER_ID, 'selectedIssues' => [3, '3', 3]]));

        $this->assertSame([3], $this->parentIds($letterInstance));
    }

    public function testKeptIssueStaysEvenIfItNoLongerMatchesGoodsOrPsv(): void
    {
        $letterInstance = $this->letter(goodsOrPsv: 'lcat_psv');
        $this->onLetter($letterInstance, $this->issue(1, 'lcat_gv'), 0);
        $this->issue(2);

        $this->mockSnapshotter->shouldReceive('snapshotRows')->once();
        $this->expectSaveAssigningIds();

        $this->sut->handleCommand(Cmd::create(['id' => self::LETTER_ID, 'selectedIssues' => [1, 2]]));

        $this->assertSame([1, 2], $this->parentIds($letterInstance));
    }

    public function testAddedIssueForTheOtherLicenceTypeIsDropped(): void
    {
        $letterInstance = $this->letter(goodsOrPsv: 'lcat_psv');
        $this->issue(1, 'lcat_gv');
        $this->issue(2, 'lcat_psv');

        $this->mockSnapshotter->shouldReceive('snapshotRows')->once();
        $this->expectSaveAssigningIds();

        $this->sut->handleCommand(Cmd::create(['id' => self::LETTER_ID, 'selectedIssues' => [1, 2]]));

        $this->assertSame([2], $this->parentIds($letterInstance));
    }

    public function testReadyLetterIsAllowed(): void
    {
        $letterInstance = $this->letter(LetterInstanceEntity::STATUS_READY);
        $this->issue(2);

        $this->mockSnapshotter->shouldReceive('snapshotRows')->once();
        $this->expectSaveAssigningIds();

        $this->sut->handleCommand(Cmd::create(['id' => self::LETTER_ID, 'selectedIssues' => [2]]));

        $this->assertSame([2], $this->parentIds($letterInstance));
    }

    public static function lockedStatuses(): array
    {
        return [
            'sent' => [LetterInstanceEntity::STATUS_SENT],
            'cancelled' => [LetterInstanceEntity::STATUS_CANCELLED],
            'failed' => [LetterInstanceEntity::STATUS_FAILED],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('lockedStatuses')]
    public function testLetterThatCanNoLongerChangeIsRejected(string $status): void
    {
        $this->letter($status);
        $this->repoMap['LetterInstance']->shouldNotReceive('save');

        $this->expectException(ValidationException::class);

        $this->sut->handleCommand(Cmd::create(['id' => self::LETTER_ID, 'selectedIssues' => []]));
    }

    /**
     * A to-do whose issue changes comes back as a new row; the edit page needs the new id to keep
     * its editor open.
     */
    public function testReportsTheNewIdOfAToDoCarriedOverToAnotherIssue(): void
    {
        $todoVersion = m::mock(LetterTodoVersion::class)->makePartial();
        $todoVersion->setId(70);

        $letterInstance = $this->letter();
        $rowA = $this->onLetter($letterInstance, $this->issue(1, null, [$todoVersion]), 0);
        $this->issue(2, null, [$todoVersion]);

        $existing = new LetterInstanceTodo();
        $existing->setId(61);
        $existing->setLetterTodoVersion($todoVersion);
        $existing->setEditedDescription(['blocks' => []]);
        $rowA->addLetterInstanceTodo($existing);
        $letterInstance->addLetterInstanceTodo($existing);
        $this->deletedTodo = $existing;

        $this->mockSnapshotter->shouldReceive('snapshotRows')->once()->with($letterInstance, m::type('array'), []);
        $this->expectSaveAssigningIds();

        $result = $this->sut->handleCommand(Cmd::create(['id' => self::LETTER_ID, 'selectedIssues' => [2]]));

        $this->assertNull($result->getFlag('confirmationRequired'), 'the edited to-do is kept, so nothing to confirm');
        $this->assertSame([61 => 901], $result->getFlag('movedTodoIds'));
    }
}
