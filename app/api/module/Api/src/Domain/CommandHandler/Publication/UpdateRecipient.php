<?php

/**
 * Update Recipient
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Publication;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;

/**
 * Update Recipient
 */
final class UpdateRecipient extends AbstractCommandHandler implements TransactionedInterface
{
    public const string ERROR_INVALID_SUBSCRIPTION = 'PUB-REC-1';

    protected $repoServiceName = 'Recipient';

    public function handleCommand(CommandInterface $command)
    {
        $recipient = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $recipient->update(
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

        $this->getRepo()->save($recipient);

        $result = new Result();
        $result->addId('recipient', $recipient->getId());
        $result->addMessage('Recipient updated successfully');

        return $result;
    }
}
