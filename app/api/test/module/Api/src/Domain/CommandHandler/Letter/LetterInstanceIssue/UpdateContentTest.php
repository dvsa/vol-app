<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Letter\LetterInstanceIssue;

use Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterInstanceIssue\UpdateContent as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\LetterInstanceIssue as LetterInstanceIssueRepo;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue as LetterInstanceIssueEntity;
use Dvsa\Olcs\Transfer\Command\Letter\LetterInstanceIssue\UpdateContent as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * UpdateContent LetterInstanceIssue Test
 */
final class UpdateContentTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('LetterInstanceIssue', LetterInstanceIssueRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $issueId = 42;
        $editedContent = '{"blocks":[{"type":"paragraph","data":{"text":"Hello world"}}],"version":"2.28.2"}';
        $expectedArray = json_decode($editedContent, true);

        $command = Cmd::create([
            'id' => $issueId,
            'editedContent' => $editedContent,
            'version' => 1,
        ]);

        $entity = m::mock(LetterInstanceIssueEntity::class)->makePartial();
        $entity->setId($issueId);

        $entity->shouldReceive('setEditedContentFromArray')
            ->with($expectedArray)
            ->once()
            ->andReturnSelf();

        $this->repoMap['LetterInstanceIssue']->shouldReceive('fetchUsingId')
            ->with($command)
            ->once()
            ->andReturn($entity);

        $this->repoMap['LetterInstanceIssue']->shouldReceive('save')
            ->with($entity)
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertSame($issueId, $result->getId('letterInstanceIssue'));
        $this->assertStringContainsString('updated successfully', (string) $result->getMessages()[0]);
    }

    private function issueOn(bool $isOther): LetterInstanceIssueEntity
    {
        $version = new \Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion();
        $version->setHeading('Other issue');
        $version->setIsOther($isOther);
        $issue = new LetterInstanceIssueEntity();
        $issue->setId(10);
        $issue->setLetterIssueVersion($version);

        return $issue;
    }

    private function save(LetterInstanceIssueEntity $issue, array $data): void
    {
        $command = Cmd::create(['id' => 10, 'editedContent' => '{"blocks":[]}', 'version' => 1] + $data);
        $this->repoMap['LetterInstanceIssue']->shouldReceive('fetchUsingId')->with($command)->andReturn($issue);
        $this->repoMap['LetterInstanceIssue']->shouldReceive('save')->with($issue)->once();

        $this->sut->handleCommand($command);
    }

    /** VOL-7735 */
    public function testHeadingIsSavedOnAnOtherIssue(): void
    {
        $issue = $this->issueOn(true);
        $this->save($issue, ['editedHeading' => '  Vehicle storage ']);
        $this->assertSame('Vehicle storage', $issue->getEditedHeading());
    }

    public function testABlankHeadingClearsIt(): void
    {
        $issue = $this->issueOn(true);
        $issue->setEditedHeading('Vehicle storage');
        $this->save($issue, ['editedHeading' => '']);
        $this->assertNull($issue->getEditedHeading());
    }

    public function testHeadingIsIgnoredOnAStandardIssue(): void
    {
        $issue = $this->issueOn(false);
        $this->save($issue, ['editedHeading' => 'Something else']);
        $this->assertNull($issue->getEditedHeading());
    }

    public function testNoHeadingSentLeavesTheHeadingAlone(): void
    {
        $issue = $this->issueOn(true);
        $issue->setEditedHeading('Vehicle storage');
        $this->save($issue, []);
        $this->assertSame('Vehicle storage', $issue->getEditedHeading());
    }

    /**
     * Final review: a sent letter's content, an Other issue's heading included, is what the operator
     * received, so it can't be changed afterwards.
     */
    public function testCannotChangeASentLetter(): void
    {
        $letter = new \Dvsa\Olcs\Api\Entity\Letter\LetterInstance();
        $letter->setStatus(new \Dvsa\Olcs\Api\Entity\System\RefData(\Dvsa\Olcs\Api\Entity\Letter\LetterInstance::STATUS_SENT));
        $issue = $this->issueOn(true);
        $letter->addLetterInstanceIssue($issue);

        $command = Cmd::create(['id' => 10, 'editedContent' => '{"blocks":[]}', 'version' => 1, 'editedHeading' => 'Changed']);
        $this->repoMap['LetterInstanceIssue']->shouldReceive('fetchUsingId')->andReturn($issue);
        $this->repoMap['LetterInstanceIssue']->shouldReceive('save')->never();

        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);
        $this->sut->handleCommand($command);
    }
}
