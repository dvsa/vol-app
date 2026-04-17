<?php

declare(strict_types=1);

/**
 * Withdraw BusReg Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Bus;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Bus\WithdrawBusReg;
use Dvsa\Olcs\Api\Domain\Repository\Bus as BusRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Bus\WithdrawBusReg as Cmd;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEbsrWithdrawn;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee as CancelFeeCmd;

/**
 * Withdraw BusReg Test
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class WithdrawBusRegTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new WithdrawBusReg();
        $this->mockRepo('Bus', BusRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            BusRegEntity::STATUS_WITHDRAWN,
            'reg_in_error'
        ];

        parent::initReferences();
    }

    /**
     *
     * @param $isEbsr
     * @param $hasOutstandingFee
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('handleCommandProvider')]
    public function testHandleCommandWithFee(mixed $isEbsr, mixed $hasOutstandingFee): void
    {
        $id = 99;
        $ebsrId = 55;

        $fees = $this->getFees($hasOutstandingFee);

        $command = Cmd::Create(
            [
                'id' => $id,
                'reason' => 'reg_in_error'
            ]
        );

        $busReg = m::mock(BusRegEntity::class);
        $busReg->shouldReceive('withdraw')
               ->once()
               ->with(
                   $this->refData[BusRegEntity::STATUS_WITHDRAWN],
                   $this->refData['reg_in_error']
               )
               ->andReturnSelf();
        $busReg->shouldReceive('getId')->once()->withNoArgs()->andReturn($id);
        $busReg->shouldReceive('isFromEbsr')->once()->withNoArgs()->andReturn($isEbsr);
        $busReg->shouldReceive('getEbsrSubmissions->first->getId')
            ->times($isEbsr ? 1 : 0)
            ->withNoArgs()
            ->andReturn($ebsrId);
        $busReg->shouldReceive('getFees')->once()->withNoArgs()->andReturn($fees);

        if ($isEbsr) {
            $this->expectedEmailQueueSideEffect(SendEbsrWithdrawn::class, ['id' => $ebsrId], $ebsrId, new Result());
        }

        if ($hasOutstandingFee) {
            $this->expectedSideEffect(CancelFeeCmd::class, ['id' => 77], new Result());
            $this->expectedSideEffect(CancelFeeCmd::class, ['id' => 78], new Result());
            $this->expectedSideEffect(CancelFeeCmd::class, ['id' => 79], new Result());
        }

        $this->repoMap['Bus']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($busReg)
            ->shouldReceive('save')
            ->with(m::type(BusRegEntity::class))
            ->once();

        $this->sut->handleCommand($command);
    }

    private function getFees(mixed $hasOutstandingFees): ArrayCollection
    {
        if (!$hasOutstandingFees) {
            return new ArrayCollection();
        }

        $fee1 = m::mock(FeeEntity::class);
        $fee1->shouldReceive('isOutstanding')->once()->withNoArgs()->andReturn(false);
        $fee1->shouldReceive('getId')->never();

        $fee2 = m::mock(FeeEntity::class);
        $fee2->shouldReceive('isOutstanding')->once()->withNoArgs()->andReturn(true);
        $fee2->shouldReceive('getId')->once()->withNoArgs()->andReturn(77);

        $fee3 = m::mock(FeeEntity::class);
        $fee3->shouldReceive('isOutstanding')->once()->withNoArgs()->andReturn(true);
        $fee3->shouldReceive('getId')->once()->withNoArgs()->andReturn(78);

        $fee4 = m::mock(FeeEntity::class);
        $fee4->shouldReceive('isOutstanding')->once()->withNoArgs()->andReturn(false);
        $fee4->shouldReceive('getId')->never();

        $fee5 = m::mock(FeeEntity::class);
        $fee5->shouldReceive('isOutstanding')->once()->withNoArgs()->andReturn(true);
        $fee5->shouldReceive('getId')->once()->withNoArgs()->andReturn(79);

        return new ArrayCollection([$fee1, $fee2, $fee3, $fee4, $fee5]);
    }

    /**
     * @return array
     */
    public static function handleCommandProvider(): array
    {
        return [
            [true, true],
            [false, true],
            [true, false],
            [false, false]
        ];
    }
}
