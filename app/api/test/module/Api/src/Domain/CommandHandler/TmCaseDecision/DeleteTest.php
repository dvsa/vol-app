<?php

declare(strict_types=1);

/**
 * Delete Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TmCaseDecision;

use Dvsa\Olcs\Api\Domain\CommandHandler\TmCaseDecision\Delete as DeleteCommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\TmCaseDecision;
use Dvsa\Olcs\Api\Entity\Tm\TmCaseDecision as TmCaseDecisionEntity;
use Dvsa\Olcs\Transfer\Command\TmCaseDecision\Delete as DeleteCommand;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Delete Test
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
        $this->mockRepo('TmCaseDecision', TmCaseDecision::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $id = 111;

        $data = [
            'id' => $id,
            'version' => 2
        ];

        $command = DeleteCommand::create($data);

        /** @var TmCaseDecisionEntity $tmCaseDecisionEntity */
        $tmCaseDecisionEntity = m::mock(TmCaseDecisionEntity::class)->makePartial();
        $tmCaseDecisionEntity->setId($command->getId());

        $this->repoMap['TmCaseDecision']->shouldReceive('fetchById')
            ->with($id)
            ->andReturn($tmCaseDecisionEntity)
            ->shouldReceive('delete')
            ->with(m::type(TmCaseDecisionEntity::class))
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\Command\Result::class, $result);
        $this->assertTrue(property_exists($result, 'ids'));
        $this->assertTrue(property_exists($result, 'messages'));
        $this->assertContains('Id 111 deleted', $result->getMessages());
    }
}
