<?php

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
class UpdateContentTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('LetterInstanceIssue', LetterInstanceIssueRepo::class);

        parent::setUp();
    }

    public function testHandleCommand()
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
        $this->assertStringContainsString('updated successfully', $result->getMessages()[0]);
    }
}
