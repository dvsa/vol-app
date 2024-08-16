<?php

/**
 * Delete Recipient
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Publication;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Delete Recipient
 */
final class DeleteRecipient extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Recipient';

    public function handleCommand(CommandInterface $command)
    {
        $recipient = $this->getRepo()->fetchUsingId($command);

        $this->getRepo()->delete($recipient);

        $result = new Result();
        $result->addId('recipient', $recipient->getId());
        $result->addMessage('Recipient deleted successfully');

        return $result;
    }
}
