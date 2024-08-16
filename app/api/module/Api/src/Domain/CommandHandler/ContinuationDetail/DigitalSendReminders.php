<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ContinuationDetail;

use Dvsa\Olcs\Api\Entity\Licence\Continuation;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create;
use Dvsa\Olcs\Api\Entity\Queue\Queue;

/**
 * Find licences with continuations that are near expiry and have not been continued yet, then create a job
 * to print continuation checklists
 */
final class DigitalSendReminders extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'ContinuationDetail';

    protected $extraRepos = ['SystemParameter'];

    /**
     * Handle command
     *
     * @param CommandInterface $command Command DTO
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $reminderPeriodDays = $this->getRepo('SystemParameter')->getDigitalContinuationReminderPeriod();
        $continuationDetails = $this->getRepo()->fetchListForDigitalReminders($reminderPeriodDays);

        /** @var Continuation $continuationDetail */
        foreach ($continuationDetails as $continuationDetail) {
            // Create a queue job for each continuation/licence
            $createCmd = Create::create(
                [
                    'entityId' => $continuationDetail->getId(),
                    'type' => Queue::TYPE_CONT_DIGITAL_REMINDER,
                    'status' => Queue::STATUS_QUEUED,
                ]
            );
            $this->handleSideEffect($createCmd);
        }

        $this->result->addMessage(count($continuationDetails) . ' reminder queue jobs created');

        return $this->result;
    }
}
