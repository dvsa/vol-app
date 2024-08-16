<?php

/**
 * Close Alerts
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\CompaniesHouse;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler as GenericAbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseAlert;

/**
 * Close Alerts
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class CloseAlerts extends GenericAbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'CompaniesHouseAlert';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        foreach ($command->getIds() as $id) {
            /** @var CompaniesHouseAlert $alert */
            $alert = $this->getRepo()->fetchById($id);
            $alert->setIsClosed('Y');
            $this->getRepo()->save($alert);
        }

        $result->addMessage(count($command->getIds()) . ' Alert(s) closed');

        return $result;
    }
}
