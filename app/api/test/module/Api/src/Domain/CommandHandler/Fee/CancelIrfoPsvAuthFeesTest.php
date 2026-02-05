<?php

declare(strict_types=1);

/**
 * CancelIrfoPsvAuthFeesTest
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Fee;

use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee as CancelFeeCommand;
use Dvsa\Olcs\Api\Domain\Command\Fee\CancelIrfoPsvAuthFees as CancelIrfoPsvAuthFeesCommand;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Fee\CancelIrfoPsvAuthFees as Sut;
use Dvsa\Olcs\Api\Domain\Repository\Fee as FeeRepo;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\System\RefData as RefDataEntity;

/**
 * CancelIrfoPsvAuthFeesTest
 */
class CancelIrfoPsvAuthFeesTest extends AbstractCommandHandlerTestCase
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
                23 => m::mock(FeeEntity::class)
                    ->makePartial()
                    ->setFeeStatus(new RefDataEntity(FeeEntity::STATUS_OUTSTANDING))
                    ->setFeeType(
                        m::mock(FeeTypeEntity::class)
                            ->shouldReceive('getFeeType')
                            ->andReturnSelf()
                            ->shouldReceive('getId')
                            ->andReturn(RefDataEntity::FEE_TYPE_IRFOPSVAPP)->getMock()
                    ),
                24 => m::mock(FeeEntity::class)
                    ->makePartial()
                    ->setFeeStatus(new RefDataEntity(FeeEntity::STATUS_OUTSTANDING))
                    ->setFeeType(
                        m::mock(FeeTypeEntity::class)
                            ->shouldReceive('getFeeType')
                            ->andReturnSelf()
                            ->shouldReceive('getId')
                            ->andReturn(RefDataEntity::FEE_TYPE_IRFOPSVCOPY)->getMock()
                    ),
                25 => m::mock(FeeEntity::class)
                    ->makePartial()
                    ->setFeeStatus(new RefDataEntity(FeeEntity::STATUS_OUTSTANDING))
                    ->setFeeType(
                        m::mock(FeeTypeEntity::class)
                            ->shouldReceive('getFeeType')
                            ->andReturnSelf()
                            ->shouldReceive('getId')
                            ->andReturn(RefDataEntity::FEE_TYPE_IRFOPSVANN)->getMock()
                    ),
                26 => m::mock(FeeEntity::class)
                    ->makePartial()
                    ->setFeeStatus(new RefDataEntity(FeeEntity::STATUS_PAID))
                    ->setFeeType(
                        m::mock(FeeTypeEntity::class)
                            ->shouldReceive('getFeeType')
                            ->andReturnSelf()
                            ->shouldReceive('getId')
                            ->andReturn(RefDataEntity::FEE_TYPE_IRFOPSVANN)->getMock()
                    ),
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $fees = [
            $this->references[FeeEntity::class][23],
            $this->references[FeeEntity::class][24],
            $this->references[FeeEntity::class][25],
            $this->references[FeeEntity::class][26]
        ];

        $this->repoMap['Fee']->shouldReceive('fetchFeesByIrfoPsvAuthId')->with(542)->once()
            ->andReturn($fees);

        // 23 shouldn't be deleted as its type is FEE_TYPE_IRFOPSVAPP
        // 26 shouldn't be cancelled as its not 'outstanding'
        $this->expectedSideEffect(CancelFeeCommand::class, ['id' => 24], new Result());
        $this->expectedSideEffect(CancelFeeCommand::class, ['id' => 25], new Result());

        $command = CancelIrfoPsvAuthFeesCommand::create(
            ['id' => 542, 'exclusions' => [RefDataEntity::FEE_TYPE_IRFOPSVAPP]]
        );
        $result = $this->sut->handleCommand($command);

        $this->assertContains('IRFO PSV Auth fees cancelled successfully', $result->getMessages());
    }
}
