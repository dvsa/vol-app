<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ContinuationDetail;

use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\ContinuationDetail\GenerateChecklistDocument as GenerateChecklistDocumentCommand;

/**
 * Generate continuation checklist for digital continuations that have not been completed
 */
final class GenerateChecklistReminder extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'ContinuationDetail';

    /**
     * Handle command
     *
     * @param CommandInterface $command Command DTO
     *
     * @return \Dvsa\Olcs\Api\Domain\Command\Result
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var ContinuationDetail $continuationDetail */
        $continuationDetail = $this->getRepo()->fetchById($command->getId());

        // Check it hasn't already been generated
        if (!$continuationDetail->getDigitalReminderSent()) {
            $this->result->merge(
                $this->handleSideEffect(
                    GenerateChecklistDocumentCommand::create(
                        [
                            'id' => $continuationDetail->getId(),
                            'user' => $command->getUser(),
                            'enforcePrint' => false,
                        ]
                    )
                )
            );

            $document = $this->getRepo()->getReference(Document::class, $this->result->getId('document'));
            $continuationDetail
                ->setChecklistDocument($document)
                ->setDigitalReminderSent(true);

            $this->getRepo()->save($continuationDetail);

            $this->result
                ->addId('continuation_detail', $continuationDetail->getId())
                ->addMessage('Reminder sent');
        } else {
            $this->result
                ->addId('continuation_detail', $continuationDetail->getId())
                ->addMessage('Reminder has already been sent');
        }

        return $this->result;
    }
}
