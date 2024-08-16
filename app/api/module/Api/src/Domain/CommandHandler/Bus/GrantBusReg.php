<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Bus;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEbsrCancelled;
use Dvsa\Olcs\Api\Domain\Command\Email\SendEbsrRegistered;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\QueueAwareTrait;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Transfer\Command\Bus\PrintLetter as BusPrintLetterCmd;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Publication\Bus as PublicationBusCmd;
use Dvsa\Olcs\Transfer\Command\Task\CloseTasks as CloseTasksCmd;

/**
 * Grant BusReg
 */
final class GrantBusReg extends AbstractCommandHandler
{
    use QueueAwareTrait;

    protected $repoServiceName = 'Bus';

    /**
     * Handle Query
     *
     * @param \Dvsa\Olcs\Transfer\Command\Bus\GrantBusReg $command Command
     *
     * @return Result
     * @throws BadRequestException
     * @throws ValidationException
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var BusRegEntity $busReg */
        $busReg = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT);

        $status = $busReg->getStatusForGrant();

        if (empty($status)) {
            throw new BadRequestException('The Bus Reg is not grantable');
        }

        $variationReasons = null;
        if ($busReg->getStatus()->getId() == BusRegEntity::STATUS_VAR) {
            if ($command->getVariationReasons() !== null) {
                // set variation reasons
                foreach ($command->getVariationReasons() as $variationReasonId) {
                    $variationReasons[] = $this->getRepo()->getRefdataReference($variationReasonId);
                }
            }

            if (empty($variationReasons)) {
                throw new ValidationException(['Variation reasons missing']);
            }
        }

        $busReg->grant(
            $this->getRepo()->getRefdataReference($status),
            $variationReasons
        );

        $this->getRepo()->save($busReg);

        $sideEffects[] = $this->getPublishCmd($busReg->getId());

        //if this is an ebsr record, send email confirmation
        if ($busReg->isFromEbsr()) {
            $status = $busReg->getStatus()->getId();
            $ebsrId = $busReg->getEbsrSubmissions()->first()->getId();

            if ($status === BusRegEntity::STATUS_REGISTERED) {
                $sideEffects[] = $this->getRegisteredEmailCmd($ebsrId);
            } else {
                $sideEffects[] = $this->getCancelledEmailCmd($ebsrId);
            }
        }

        // Print licence
        $busPrintLetterData = [
            'id' => $busReg->getId(),
        ];

        $sideEffects[] = BusPrintLetterCmd::create($busPrintLetterData);
        $sideEffects = $this->addCloseTasksSideEffect($busReg, $sideEffects);

        //  process side effects
        $this->handleSideEffects($sideEffects);

        $result = new Result();
        $result->addId('bus', $busReg->getId());
        $result->addMessage('Bus Reg granted successfully');

        return $result;
    }

    /**
     * Retrieve ids of open tasks, and add a side effect to close
     *
     * @param BusRegEntity $busReg      bus reg entity
     * @param array        $sideEffects existing side effects
     *
     * @return array
     */
    private function addCloseTasksSideEffect(BusRegEntity $busReg, array $sideEffects)
    {
        $openTasks = $busReg->getOpenTaskIds();

        if (!empty($openTasks)) {
            $sideEffects[] = CloseTasksCmd::create(['ids' => $openTasks]);
        }

        return $sideEffects;
    }

    /**
     * Get publish command
     *
     * @param int $busRegId Bus registration id
     *
     * @return PublicationBusCmd
     */
    private function getPublishCmd($busRegId)
    {
        return PublicationBusCmd::create(['id' => $busRegId]);
    }

    /**
     * Get Cancelled email command
     *
     * @param int $ebsrId Ebrs Id
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Queue\Create
     */
    private function getCancelledEmailCmd($ebsrId)
    {
        return $this->emailQueue(SendEbsrCancelled::class, ['id' => $ebsrId], $ebsrId);
    }

    /**
     * Get Registered Email command
     *
     * @param int $ebsrId Ebrs Id
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Queue\Create
     */
    private function getRegisteredEmailCmd($ebsrId)
    {
        return $this->emailQueue(SendEbsrRegistered::class, ['id' => $ebsrId], $ebsrId);
    }
}
