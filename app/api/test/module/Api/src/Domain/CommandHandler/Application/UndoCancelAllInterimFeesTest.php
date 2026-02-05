<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UndoCancelAllInterimFees;
use Dvsa\Olcs\Api\Domain\Command\Application\UndoCancelAllInterimFees as UndoCancelAllInterimFeesCmd;

/**
 * UndoCancelAllInterimFees test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class UndoCancelAllInterimFeesTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UndoCancelAllInterimFees();
        $this->mockRepo('Fee', \Dvsa\Olcs\Api\Domain\Repository\Fee::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            FeeEntity::STATUS_OUTSTANDING => m::mock(FeeEntity::class)
        ];

        $this->references = [
            FeeEntity::class => [
                23 => m::mock(FeeEntity::class)
                    ->shouldReceive('isCancelled')
                    ->once()
                    ->andReturn(true)
                    ->shouldReceive('setFeeStatus')
                    ->with($this->refData[FeeEntity::STATUS_OUTSTANDING])
                    ->once()
                    ->getMock(),
                24 => m::mock(FeeEntity::class)
                    ->shouldReceive('isCancelled')
                    ->once()
                    ->andReturn(true)
                    ->shouldReceive('setFeeStatus')
                    ->with($this->refData[FeeEntity::STATUS_OUTSTANDING])
                    ->once()
                    ->getMock(),
                25 => m::mock(FeeEntity::class)
                    ->shouldReceive('isCancelled')
                    ->once()
                    ->andReturn(false)
                    ->getMock(),
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $fees = [
            $this->references[FeeEntity::class][23],
            $this->references[FeeEntity::class][24],
            $this->references[FeeEntity::class][25]
        ];

        $this->repoMap['Fee']
            ->shouldReceive('fetchInterimFeesByApplicationId')
            ->with(542)
            ->once()
            ->andReturn($fees)
            ->shouldReceive('save')
            ->with($this->references[FeeEntity::class][23])
            ->once()
            ->shouldReceive('save')
            ->with($this->references[FeeEntity::class][24])
            ->once();

        $command = UndoCancelAllInterimFeesCmd::create(['id' => 542]);
        $result = $this->sut->handleCommand($command);

        $this->assertContains('All existing cancelled interim fees set back to outstanding', $result->getMessages());
    }
}
