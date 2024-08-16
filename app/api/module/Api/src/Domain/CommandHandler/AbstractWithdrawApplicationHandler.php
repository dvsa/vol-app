<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler;

use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\QueueAwareTrait;
use Dvsa\Olcs\Api\Domain\Repository\RepositoryInterface;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\WithdrawableInterface;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\WithdrawApplicationInterface;

/**
 * Abstract handler to withdraw an application
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
abstract class AbstractWithdrawApplicationHandler extends AbstractCommandHandler implements AuthAwareInterface
{
    use AuthAwareTrait;
    use QueueAwareTrait;

    protected $repoServiceName = 'changeMe';
    protected $withdrawStatus = IrhpInterface::STATUS_WITHDRAWN; //override for non-permits entities
    protected $confirmationMessage = 'Application withdrawn';
    protected $cancelFees = true;
    protected $sideEffects = [];

    /**
     * Handle command
     *
     * @param CommandInterface $command command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var RepositoryInterface          $repo
         * @var WithdrawableInterface        $application
         * @var WithdrawApplicationInterface $command
         */
        $repo = $this->getRepo();
        $id = $command->getId();
        $withdrawReason = $command->getReason();

        $application = $repo->fetchById($id);
        $checkReasonAgainstStatus = !$this->isInternalUser();

        $application->withdraw(
            $this->refData($this->withdrawStatus),
            $this->refData($withdrawReason),
            $checkReasonAgainstStatus
        );

        $repo->save($application);

        //Optionally cancel outstanding fees, defaults to true
        if ($this->cancelFees) {
            $outstandingFees = $application->getOutstandingFees();

            /** @var Fee $fee */
            foreach ($outstandingFees as $fee) {
                $this->sideEffects[] = CancelFee::create(['id' => $fee->getId()]);
            }
        }

        $emailCommand = $application->getAppWithdrawnEmailCommand($withdrawReason);
        if ($emailCommand) {
            $this->sideEffects[] = $this->emailQueue($emailCommand, ['id' => $id], $id);
        }

        $this->handleSideEffects($this->sideEffects);

        $this->result->addId($this->repoServiceName, $command->getId());
        $this->result->addMessage($this->confirmationMessage);

        return $this->result;
    }
}
