<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\CommandHandler\Document\OverrideDocumentAnalysisFlag;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\DocumentAnalysis as DocumentAnalysisRepo;
use Dvsa\Olcs\Api\Entity\Doc\DocumentAnalysis as Entity;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Service\Idp\AnalysisAnnotationOverlay;
use Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser\AnalysisResultNormaliser;
use Dvsa\Olcs\Api\Service\Idp\AnalysisResultNormaliser\NormalisedResult;
use Dvsa\Olcs\Transfer\Command\Document\OverrideDocumentAnalysisFlag as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;
use PHPUnit\Framework\Attributes\DataProvider;

final class OverrideDocumentAnalysisFlagTest extends AbstractCommandHandlerTestCase
{
    private const STORED = ['version' => 1, 'rows' => ['stored' => 'payload']];

    private User|m\MockInterface $user;
    private AnalysisResultNormaliser|m\MockInterface $normaliser;

    public function setUp(): void
    {
        $this->normaliser = m::mock(AnalysisResultNormaliser::class);
        $this->sut = new OverrideDocumentAnalysisFlag($this->normaliser, new AnalysisAnnotationOverlay());
        $this->mockRepo('DocumentAnalysis', DocumentAnalysisRepo::class);

        $this->user = m::mock(User::class);
        $this->user->allows('getId')->andReturn(99);
        $this->user->allows('getContactDetails')->andReturnNull();
        $this->user->allows('getLoginId')->andReturn('jbloggs');

        $this->mockedSmServices = [AuthorizationService::class => m::mock(AuthorizationService::class)];
        $this->mockedSmServices[AuthorizationService::class]
            ->allows('getIdentity->getUser')
            ->andReturn($this->user);

        parent::setUp();
    }

    public static function overridableProvider(): \Iterator
    {
        yield 'fail' => ['fail'];
        yield 'skipped' => ['skipped'];
    }

    #[DataProvider('overridableProvider')]
    public function testRecordsTheChangeAsAnAnnotationAndLeavesTheResultAlone(string $flag): void
    {
        $command = Cmd::create(['id' => 5, 'row' => 'largeDeposit', 'comment' => 'Checked by hand', 'changeRecommendation' => 'Y']);

        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')->with($command)
            ->andReturn($this->analysis(Entity::STATUS_SUCCESS, null, 3));
        $this->normaliser->expects('fromStored')->with(self::STORED)->andReturn($this->normalisedResult($flag));
        $this->repoMap['DocumentAnalysis']->expects('recordAnnotations')
            ->with(5, m::on(static function (array $annotations) use ($flag): bool {
                $row = $annotations['rows']['largeDeposit'] ?? null;

                return $row !== null
                    && $row['flag'] === 'pass'
                    && $row['originalFlag'] === $flag
                    && $row['comment'] === 'Checked by hand'
                    && $row['changedBy'] === ['id' => 99, 'name' => 'jbloggs'];
            }), 3, $this->user)
            ->andReturn(1);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(5, $result->getId('documentAnalysis'));
        $this->assertSame(['Document analysis 5 check largeDeposit changed to pass'], $result->getMessages());
    }

    /**
     * changeRecommendation "N" with a comment still given is saved as a note: the annotation's
     * flag is the row's own current flag, so the row is not promoted to a pass.
     */
    public function testANoteWithoutChangingTheFlagRecordsTheCommentOnly(): void
    {
        $command = Cmd::create(['id' => 5, 'row' => 'largeDeposit', 'comment' => 'Just a note', 'changeRecommendation' => 'N']);

        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')->with($command)
            ->andReturn($this->analysis(Entity::STATUS_SUCCESS, null, 3));
        $this->normaliser->expects('fromStored')->with(self::STORED)->andReturn($this->normalisedResult('fail'));
        $this->repoMap['DocumentAnalysis']->expects('recordAnnotations')
            ->with(5, m::on(static function (array $annotations): bool {
                $row = $annotations['rows']['largeDeposit'] ?? null;

                return $row !== null
                    && $row['flag'] === 'fail'
                    && $row['originalFlag'] === 'fail'
                    && $row['comment'] === 'Just a note';
            }), 3, $this->user)
            ->andReturn(1);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(['Document analysis 5 check largeDeposit comment saved'], $result->getMessages());
    }

    public function testKeepsEarlierAnnotationsOnOtherRows(): void
    {
        $command = Cmd::create(['id' => 5, 'row' => 'largeDeposit', 'comment' => 'Because', 'changeRecommendation' => 'Y']);

        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')
            ->andReturn($this->analysis(Entity::STATUS_SUCCESS, $this->earlierAnnotationOnName(), 4));
        $this->normaliser->expects('fromStored')->andReturn($this->normalisedResult('fail'));
        $this->repoMap['DocumentAnalysis']->expects('recordAnnotations')
            ->with(
                5,
                m::on(static fn(array $a): bool => isset($a['rows']['name'], $a['rows']['largeDeposit'])),
                4,
                $this->user
            )
            ->andReturn(1);

        $this->sut->handleCommand($command);
    }

    public static function notSuccessfulProvider(): \Iterator
    {
        yield 'pending' => [Entity::STATUS_PENDING];
        yield 'error' => [Entity::STATUS_ERROR];
        yield 'timeout' => [Entity::STATUS_TIMEOUT];
    }

    #[DataProvider('notSuccessfulProvider')]
    public function testRefusesAnAnalysisThatIsNotSuccessful(string $status): void
    {
        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')->andReturn($this->analysis($status));
        $this->normaliser->shouldNotReceive('fromStored');
        $this->repoMap['DocumentAnalysis']->shouldNotReceive('recordAnnotations');

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage(sprintf('Document analysis 5 is %s, not SUCCESS', $status));

        $this->sut->handleCommand(Cmd::create(['id' => 5, 'row' => 'name', 'comment' => 'x']));
    }

    public function testRefusesAnAnalysisWithNoAssessment(): void
    {
        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')->andReturn($this->analysis(Entity::STATUS_SUCCESS));
        $this->normaliser->expects('fromStored')->andReturnNull();
        $this->repoMap['DocumentAnalysis']->shouldNotReceive('recordAnnotations');

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Document analysis 5 has no assessment to change');

        $this->sut->handleCommand(Cmd::create(['id' => 5, 'row' => 'name', 'comment' => 'x']));
    }

    public function testRefusesARowThatIsAlreadyAPass(): void
    {
        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')->andReturn($this->analysis(Entity::STATUS_SUCCESS));
        $this->normaliser->expects('fromStored')->andReturn($this->normalisedResult('pass'));
        $this->repoMap['DocumentAnalysis']->shouldNotReceive('recordAnnotations');

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('Check name of document analysis 5 is not a fail or skipped');

        $this->sut->handleCommand(Cmd::create(['id' => 5, 'row' => 'name', 'comment' => 'x']));
    }

    /**
     * The caseworker's answer can be revisited: which rows can be answered is asked of the
     * analyser's own flag, so a row already changed to a pass is still theirs to edit. "Y" again
     * keeps it a pass and replaces the comment.
     */
    public function testYesOnARowAlreadyChangedToAPassUpdatesTheComment(): void
    {
        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')
            ->andReturn($this->analysis(Entity::STATUS_SUCCESS, $this->earlierAnnotationOnName(), 3));
        $this->normaliser->expects('fromStored')->andReturn($this->normalisedResult('fail'));
        $this->repoMap['DocumentAnalysis']->expects('recordAnnotations')
            ->with(5, m::on(static fn(array $a): bool => $a['rows']['name']['flag'] === 'pass'
                && $a['rows']['name']['originalFlag'] === 'fail'
                && $a['rows']['name']['comment'] === 'A better reason'), 3, $this->user)
            ->andReturn(1);

        $result = $this->sut->handleCommand(
            Cmd::create(['id' => 5, 'row' => 'name', 'comment' => 'A better reason', 'changeRecommendation' => 'Y'])
        );

        $this->assertSame(['Document analysis 5 check name changed to pass'], $result->getMessages());
    }

    /** "N" on a row that was changed to a pass changes it back to the flag the analyser gave it. */
    public function testNoOnARowChangedToAPassChangesItBack(): void
    {
        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')
            ->andReturn($this->analysis(Entity::STATUS_SUCCESS, $this->earlierAnnotationOnName(), 3));
        $this->normaliser->expects('fromStored')->andReturn($this->normalisedResult('fail'));
        $this->repoMap['DocumentAnalysis']->expects('recordAnnotations')
            ->with(5, m::on(static fn(array $a): bool => $a['rows']['name']['flag'] === 'fail'
                && $a['rows']['name']['originalFlag'] === 'fail'
                && $a['rows']['name']['comment'] === 'Changed in error'), 3, $this->user)
            ->andReturn(1);

        $result = $this->sut->handleCommand(
            Cmd::create(['id' => 5, 'row' => 'name', 'comment' => 'Changed in error', 'changeRecommendation' => 'N'])
        );

        $this->assertSame(['Document analysis 5 check name changed back to fail'], $result->getMessages());
    }

    /**
     * "N" with nothing to say deletes the caseworker's entry for the row, so it reads exactly as
     * the analyser left it: a change is undone and a note removed alike. Other rows' entries are kept.
     */
    #[DataProvider('existingEntryProvider')]
    public function testNoWithNoCommentDeletesTheCaseworkersEntryForTheRow(string $entryFlag, string $message): void
    {
        $annotations = $this->earlierAnnotationOnName();
        $annotations['rows']['name']['flag'] = $entryFlag;
        $annotations['rows']['largeDeposit'] = $annotations['rows']['name'];

        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')
            ->andReturn($this->analysis(Entity::STATUS_SUCCESS, $annotations, 3));
        $this->normaliser->expects('fromStored')->andReturn($this->normalisedResult('fail'));
        $this->repoMap['DocumentAnalysis']->expects('recordAnnotations')
            ->with(5, m::on(static fn(array $a): bool => array_keys($a['rows']) === ['largeDeposit']), 3, $this->user)
            ->andReturn(1);

        $result = $this->sut->handleCommand(
            Cmd::create(['id' => 5, 'row' => 'name', 'comment' => '', 'changeRecommendation' => 'N'])
        );

        $this->assertSame([$message], $result->getMessages());
    }

    public static function existingEntryProvider(): \Iterator
    {
        yield 'a change to a pass' => ['pass', 'Document analysis 5 check name changed back to fail'];
        yield 'a note' => ['fail', 'Document analysis 5 check name comment removed'];
    }

    /** Nothing to delete and nothing to say: nothing is written, so the version is not bumped. */
    public function testNoWithNoCommentOnARowNobodyHasAnsweredWritesNothing(): void
    {
        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')
            ->andReturn($this->analysis(Entity::STATUS_SUCCESS, null, 3));
        $this->normaliser->expects('fromStored')->andReturn($this->normalisedResult('fail'));
        $this->repoMap['DocumentAnalysis']->shouldNotReceive('recordAnnotations');

        $result = $this->sut->handleCommand(
            Cmd::create(['id' => 5, 'row' => 'name', 'comment' => '', 'changeRecommendation' => 'N'])
        );

        $this->assertSame(['Document analysis 5 check name left as it is'], $result->getMessages());
    }

    /** Changing a genuine fail to a pass needs a reason, whoever the caller is. */
    #[DataProvider('noReasonProvider')]
    public function testYesWithoutAReasonOnAFailIsRefused(string $comment): void
    {
        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')
            ->andReturn($this->analysis(Entity::STATUS_SUCCESS, null, 3));
        $this->normaliser->expects('fromStored')->andReturn($this->normalisedResult('fail'));
        $this->repoMap['DocumentAnalysis']->shouldNotReceive('recordAnnotations');

        try {
            $this->sut->handleCommand(
                Cmd::create(['id' => 5, 'row' => 'name', 'comment' => $comment, 'changeRecommendation' => 'Y'])
            );
            $this->fail('Expected the change to be refused for want of a reason');
        } catch (ValidationException $e) {
            $this->assertSame(
                [OverrideDocumentAnalysisFlag::ERR_REASON_REQUIRED => OverrideDocumentAnalysisFlag::MSG_REASON_REQUIRED],
                $e->getMessages()
            );
        }
    }

    public static function noReasonProvider(): \Iterator
    {
        yield 'empty' => [''];
        yield 'only spaces' => ['   '];
    }

    /** A skipped check was never assessed, so changing it to a pass needs no reason. */
    public function testYesWithoutAReasonOnASkippedCheckIsAllowed(): void
    {
        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')
            ->andReturn($this->analysis(Entity::STATUS_SUCCESS, null, 3));
        $this->normaliser->expects('fromStored')->andReturn($this->normalisedResult('skipped'));
        $this->repoMap['DocumentAnalysis']->expects('recordAnnotations')
            ->with(5, m::on(static fn(array $a): bool => $a['rows']['name']['flag'] === 'pass'
                && $a['rows']['name']['originalFlag'] === 'skipped'
                && $a['rows']['name']['comment'] === ''), 3, $this->user)
            ->andReturn(1);

        $result = $this->sut->handleCommand(
            Cmd::create(['id' => 5, 'row' => 'name', 'comment' => '', 'changeRecommendation' => 'Y'])
        );

        $this->assertSame(['Document analysis 5 check name changed to pass'], $result->getMessages());
    }

    public function testRefusesAnUnknownRow(): void
    {
        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')->andReturn($this->analysis(Entity::STATUS_SUCCESS));
        $this->normaliser->expects('fromStored')->andReturn($this->normalisedResult('fail'));
        $this->repoMap['DocumentAnalysis']->shouldNotReceive('recordAnnotations');

        $this->expectException(BadRequestException::class);

        $this->sut->handleCommand(Cmd::create(['id' => 5, 'row' => 'nonsense', 'comment' => 'x']));
    }

    /** Someone else changed the row between the read and the version-guarded write. */
    public function testThrowsWhenTheVersionGuardedWriteMatchesNothing(): void
    {
        $this->repoMap['DocumentAnalysis']->expects('fetchUsingId')->andReturn($this->analysis(Entity::STATUS_SUCCESS));
        $this->normaliser->expects('fromStored')->andReturn($this->normalisedResult('fail'));
        $this->repoMap['DocumentAnalysis']->expects('recordAnnotations')->andReturn(0);

        $this->expectException(BadRequestException::class);
        $this->expectExceptionMessage('changed while it was being reviewed');

        $this->sut->handleCommand(Cmd::create(['id' => 5, 'row' => 'name', 'comment' => 'x']));
    }

    private function earlierAnnotationOnName(): array
    {
        return ['rows' => ['name' => [
            'flag' => 'pass',
            'originalFlag' => 'fail',
            'comment' => 'x',
            'changedBy' => ['id' => 1, 'name' => 'A'],
            'changedOn' => '2026-10-01 10:00:00',
        ]]];
    }

    private function analysis(string $status, ?array $annotations = null, int $version = 1): Entity|m\MockInterface
    {
        $analysis = m::mock(Entity::class);
        $analysis->allows('getStatus')->andReturn($status);
        $analysis->allows('getResultNormalised')->andReturn(self::STORED);
        $analysis->allows('getAnnotations')->andReturn($annotations);
        $analysis->allows('getVersion')->andReturn($version);

        return $analysis;
    }

    private function normalisedResult(string $flag): NormalisedResult
    {
        $rows = ['bank' => ['flag' => null, 'remark' => null, 'value' => 'Example Bank', 'checks' => []]];

        foreach (['authenticity', 'name', 'statementDate', 'statementPeriod', 'averageFunds', 'largeDeposit'] as $key) {
            $rows[$key] = ['flag' => $flag, 'remark' => null, 'value' => null, 'checks' => []];
        }

        return NormalisedResult::fromRows($rows);
    }
}
