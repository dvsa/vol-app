<?php

/**
 * Create Recipient
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Publication;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Publication\Recipient;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\Olcs\Transfer\Command\Publication\CreateRecipient as Cmd;

/**
 * Create Recipient
 */
final class CreateRecipient extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Recipient';

    public function handleCommand(CommandInterface $command)
    {
        // create and save a record
        $recipient = $this->createRecipientObject($command);
        $this->getRepo()->save($recipient);

        $result = new Result();
        $result->addId('recipient', $recipient->getId());
        $result->addMessage('Recipient created successfully');

        return $result;
    }

    /**
     * @return Recipient
     */
    private function createRecipientObject(Cmd $command)
    {
        $recipient = new Recipient(
            $command->getIsObjector(),
            $command->getContactName(),
            $command->getEmailAddress(),
            $command->getSendAppDecision(),
            $command->getSendNoticesProcs()
        );

        $trafficAreas = [];
        foreach ($command->getTrafficAreas() as $trafficAreaId) {
            $trafficAreas[] = $this->getRepo()->getReference(TrafficArea::class, $trafficAreaId);
        }
        $recipient->setTrafficAreas($trafficAreas);

        if ($command->getIsPolice() !== null) {
            $recipient->setIsPolice($command->getIsPolice());
        }

        return $recipient;
    }
}
