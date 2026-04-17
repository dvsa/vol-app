<?php

declare(strict_types=1);

/**
 * Delete Letter Issue Type Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Letter\LetterIssueType;

use Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterIssueType\Delete as DeleteCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\LetterIssueType;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueType as LetterIssueTypeEntity;
use Dvsa\Olcs\Transfer\Command\Letter\LetterIssueType\Delete as DeleteCommand;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Delete Letter Issue Type Test
 */
class DeleteTest extends AbstractCommandHandlerTestCase
{
    /**
     * @var DeleteCommandHandler
     */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new DeleteCommandHandler();
        $this->mockRepo('LetterIssueType', LetterIssueType::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $id = 99;

        $data = [
            'id' => $id,
            'version' => 1
        ];

        $command = DeleteCommand::create($data);

        /** @var LetterIssueTypeEntity $letterIssueType */
        $letterIssueTypeEntity = m::mock(LetterIssueTypeEntity::class)->makePartial();
        $letterIssueTypeEntity->setId($command->getId());

        /** @var $letterIssueType LetterIssueTypeEntity */
        $letterIssueType = null;

        $this->repoMap['LetterIssueType']->shouldReceive('fetchById')
            ->with($id)
            ->andReturn($letterIssueTypeEntity)
            ->shouldReceive('delete')
            ->with(m::type(LetterIssueTypeEntity::class))
            ->andReturnUsing(
                function (LetterIssueTypeEntity $lit) use (&$letterIssueType) {
                    $letterIssueType = $lit;
                    $letterIssueType->setId(99);
                }
            )
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\Command\Result::class, $result);
        $this->assertTrue(property_exists($result, 'ids'));
        $this->assertTrue(property_exists($result, 'messages'));
        $this->assertContains('Id 99 deleted', $result->getMessages());
    }
}
