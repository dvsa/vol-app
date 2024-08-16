<?php

/**
 * DeleteTransportManagerLicence.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Tm;

use Dvsa\Olcs\Api\Domain\CacheAwareInterface;
use Dvsa\Olcs\Api\Domain\CacheAwareTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerLicence;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Delete Transport Manager Licence
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
final class DeleteTransportManagerLicence extends AbstractCommandHandler implements TransactionedInterface, CacheAwareInterface
{
    use CacheAwareTrait;

    protected $repoServiceName = 'TransportManagerLicence';

    public function handleCommand(CommandInterface $command)
    {
        $transportManagers = $this->getRepo()->fetchForLicence($command->getLicence());

        /** @var TransportManagerLicence $transportManager */
        foreach ($transportManagers as $transportManager) {
            $this->clearEntityUserCaches($transportManager->getTransportManager());
            $this->getRepo()->delete($transportManager);
        }

        $result = new Result();
        $result->addMessage('Removed transport managers for licence.');

        return $result;
    }
}
