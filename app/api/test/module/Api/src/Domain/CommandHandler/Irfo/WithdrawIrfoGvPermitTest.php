<?php

declare(strict_types=1);

/**
 * Withdraw Irfo Gv Permit Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Irfo;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Fee\CancelIrfoGvPermitFees as CancelFeesDto;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Irfo\WithdrawIrfoGvPermit as Sut;
use Dvsa\Olcs\Api\Domain\Repository\IrfoGvPermit as IrfoGvPermitRepo;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit as IrfoGvPermitEntity;
use Dvsa\Olcs\Transfer\Command\Irfo\WithdrawIrfoGvPermit as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Withdraw Irfo Gv Permit Test
 */
class WithdrawIrfoGvPermitTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Sut();
        $this->mockRepo('IrfoGvPermit', IrfoGvPermitRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            IrfoGvPermitEntity::STATUS_WITHDRAWN,
        ];

        parent::initReferences();
    }

    /**
     * testHandleCommand
     */
    public function testHandleCommand(): void
    {
        $id = 99;

        $command = Cmd::Create(
            [
                'id' => $id,
            ]
        );

        /** @var IrfoGvPermitEntity $irfoGvPermit */
        $irfoGvPermit = m::mock(IrfoGvPermitEntity::class);
        $irfoGvPermit->shouldReceive('withdraw')
            ->once()
            ->with($this->refData[IrfoGvPermitEntity::STATUS_WITHDRAWN])
            ->shouldReceive('getId')
            ->andReturn($id);

        $this->repoMap['IrfoGvPermit']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($irfoGvPermit)
            ->shouldReceive('save')
            ->with(m::type(IrfoGvPermitEntity::class))
            ->once();

        $this->expectedSideEffect(CancelFeesDto::class, ['id' => $id], new Result());

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
    }
}
