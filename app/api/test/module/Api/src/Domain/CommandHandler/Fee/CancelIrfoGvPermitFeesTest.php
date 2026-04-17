<?php

declare(strict_types=1);

/**
 * CancelIrfoGvPermitFeesTest
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Fee;

use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee as CancelFeeCommand;
use Dvsa\Olcs\Api\Domain\Command\Fee\CancelIrfoGvPermitFees as CancelIrfoGvPermitFeesCommand;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Fee\CancelIrfoGvPermitFees as Sut;
use Dvsa\Olcs\Api\Domain\Repository\Fee as FeeRepo;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * CancelIrfoGvPermitFeesTest
 */
class CancelIrfoGvPermitFeesTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Sut();
        $this->mockRepo('Fee', FeeRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->references = [
            FeeEntity::class => [
                23 => m::mock(FeeEntity::class),
                24 => m::mock(FeeEntity::class),
                25 => m::mock(FeeEntity::class)
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

        $this->repoMap['Fee']->shouldReceive('fetchFeesByIrfoGvPermitId')->with(542)->once()
            ->andReturn($fees);

        $this->expectedSideEffect(CancelFeeCommand::class, ['id' => 23], new Result());
        $this->expectedSideEffect(CancelFeeCommand::class, ['id' => 24], new Result());
        $this->expectedSideEffect(CancelFeeCommand::class, ['id' => 25], new Result());

        $command = CancelIrfoGvPermitFeesCommand::create(['id' => 542]);
        $result = $this->sut->handleCommand($command);

        $this->assertContains('IRFO GV Permit fees cancelled successfully', $result->getMessages());
    }
}
