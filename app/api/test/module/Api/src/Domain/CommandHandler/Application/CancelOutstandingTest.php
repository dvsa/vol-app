<?php

declare(strict_types=1);

/**
 * CancelOutstandingTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\CancelOutstandingFees;

/**
 * CancelOutstandingTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CancelOutstandingTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CancelOutstandingFees();
        $this->mockRepo('Fee', \Dvsa\Olcs\Api\Domain\Repository\Fee::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
        ];

        $this->references = [
            FeeEntity::class => [
                23 => m::mock(FeeEntity::class)
                    ->shouldReceive('isFullyOutstanding')
                    ->andReturn(true)
                    ->getMock(),
                24 => m::mock(FeeEntity::class)
                    ->shouldReceive('isFullyOutstanding')
                    ->andReturn(true)
                    ->getMock(),
                25 => m::mock(FeeEntity::class)
                    ->shouldReceive('isFullyOutstanding')
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

        $this->repoMap['Fee']->shouldReceive('fetchOutstandingFeesByApplicationId')->with(542)->once()
            ->andReturn($fees);

        $this->expectedSideEffect(\Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee::class, ['id' => 23], new Result());
        $this->expectedSideEffect(\Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee::class, ['id' => 24], new Result());

        $command = \Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee::create(['id' => 542]);
        $result = $this->sut->handleCommand($command);

        $this->assertContains('CancelOutstandingFees success', $result->getMessages());
    }
}
