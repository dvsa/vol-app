<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Bus;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\QueueAwareTrait;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEbsrWithdrawn;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreateQueue;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee as CancelFeeCmd;
use Dvsa\Olcs\Transfer\Command\Bus\WithdrawBusReg as WithdrawBusRegCmd;

/**
 * Withdraw BusReg
 */
final class WithdrawBusReg extends AbstractCommandHandler
{
    use QueueAwareTrait;

    protected $repoServiceName = 'Bus';

    /**
     * handle command
     *
     * @param CommandInterface|WithdrawBusRegCmd $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var BusRegEntity $busReg
         * @var FeeEntity $fee
         */
        $busReg = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT);
        $busReg->withdraw(
            $this->getRepo()->getRefdataReference(BusRegEntity::STATUS_WITHDRAWN),
            $this->getRepo()->getRefdataReference($command->getReason())
        );

        $this->getRepo()->save($busReg);

        $result = new Result();
        $result->addId('bus', $busReg->getId());
        $result->addMessage('Bus Reg withdrawn successfully');

        $fees = $busReg->getFees();

        foreach ($fees as $fee) {
            if ($fee->isOutstanding()) {
                $cancelFeeCmd = CancelFeeCmd::create(['id' => $fee->getId()]);
                $result->merge($this->handleSideEffect($cancelFeeCmd));
            }
        }

        if ($busReg->isFromEbsr()) {
            $ebsrId = $busReg->getEbsrSubmissions()->first()->getId();
            $result->merge($this->handleSideEffect($this->createEbsrWithdrawnCmd($ebsrId)));
        }

        return $result;
    }

    /**
     * Create command to queue EBSR withdrawn email
     *
     * @param int $ebsrId ebsr id
     *
     * @return CreateQueue
     */
    private function createEbsrWithdrawnCmd($ebsrId)
    {
        return $this->emailQueue(SendEbsrWithdrawn::class, ['id' => $ebsrId], $ebsrId);
    }
}
