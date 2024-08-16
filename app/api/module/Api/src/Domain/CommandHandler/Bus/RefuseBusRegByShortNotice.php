<?php

/**
 * Refuse BusReg By Short Notice
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Bus;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\QueueAwareTrait;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEbsrRefusedBySn;
use Dvsa\Olcs\Transfer\Command\Bus\RefuseBusRegByShortNotice as RefuseCmd;

/**
 * Refuse BusReg By Short Notice
 */
final class RefuseBusRegByShortNotice extends AbstractCommandHandler
{
    use QueueAwareTrait;

    protected $repoServiceName = 'Bus';

    /**
     * handle command
     *
     * @param CommandInterface|RefuseCmd $command command to refuse the bus reg by short notice
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var BusRegEntity $busReg */
        $busReg = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT);
        $busReg->refuseByShortNotice(
            $command->getReason()
        );

        $this->getRepo()->save($busReg);

        $result = new Result();
        $result->addId('bus', $busReg->getId());
        $result->addMessage('Bus Reg refused successfully');

        if ($busReg->isFromEbsr()) {
            $ebsrId = $busReg->getEbsrSubmissions()->first()->getId();
            $result->merge($this->handleSideEffect($this->createEbsrRefusedBySnCmd($ebsrId)));
        }

        return $result;
    }

    /**
     * Create command to send confirmation of short notice refusal
     *
     * @param int $ebsrId ebsr submission id
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Queue\Create
     */
    private function createEbsrRefusedBySnCmd($ebsrId)
    {
        return $this->emailQueue(SendEbsrRefusedBySn::class, ['id' => $ebsrId], $ebsrId);
    }
}
