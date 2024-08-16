<?php

/**
 * Delete Partner
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\User;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Delete Partner
 */
final class DeletePartner extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Partner';

    public function handleCommand(CommandInterface $command)
    {
        $partner = $this->getRepo()->fetchUsingId($command);

        $this->getRepo()->delete($partner);

        $result = new Result();
        $result->addId('partner', $partner->getId());
        $result->addMessage('Partner deleted successfully');

        return $result;
    }
}
