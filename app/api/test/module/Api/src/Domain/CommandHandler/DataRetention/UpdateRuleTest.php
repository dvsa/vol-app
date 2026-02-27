<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\DataRetention;

use Dvsa\Olcs\Api\Domain\CommandHandler\DataRetention\UpdateRule as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\DataRetentionRule as Repo;
use Dvsa\Olcs\Api\Entity\DataRetentionRule as Entity;
use Dvsa\Olcs\Transfer\Command\DataRetention\UpdateRule as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * UpdateRule command handler test
 *
 */
class UpdateRuleTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('DataRetentionRule', Repo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $id = 10;

        $params = [
            'id' => 10,
            'description' => 'Lorem ipsum',
            'retentionPeriod' => 60,
            'maxDataSet' => 1000,
            'isEnabled' => true,
            'actionType' => 'Automate'
        ];

        $command = Command::create($params);
        $repo = $this->repoMap['DataRetentionRule'];
        $mockRule = m::mock(Entity::class);

        $this->repoMap['DataRetentionRule']
            ->shouldReceive('fetchById')
            ->once()
            ->with($command->getId())
            ->andReturn($mockRule);

        $mockRule
            ->shouldReceive('setDescription')
            ->once()
            ->with($command->getDescription())
            ->andReturnSelf()
            ->shouldReceive('setRetentionPeriod')
            ->once()
            ->with($command->getRetentionPeriod())
            ->andReturnSelf()
            ->shouldReceive('setMaxDataSet')
            ->once()
            ->with($command->getMaxDataSet())
            ->andReturnSelf()
            ->shouldReceive('setIsEnabled')
            ->once()
            ->with($command->getIsEnabled())
            ->andReturnSelf()
            ->shouldReceive('setActionType')
            ->once()
            ->with($repo->getRefdataReference($command->getActionType()))
            ->andReturnSelf()
            ->shouldReceive('getId')
            ->andReturn($id);

        $repo
            ->shouldReceive('save')
            ->once()
            ->andReturnSelf();

        $response = $this->sut->handleCommand($command);

        $this->assertEquals(['data-retention-rule' => $id], $response->getIds());
        $this->assertEquals(['Rule updated successfully'], $response->getMessages());
    }
}
