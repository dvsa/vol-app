<?php

/**
 * Update Service Register
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Bus;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Update Service Register
 */
final class UpdateServiceRegister extends AbstractCommandHandler
{
    protected $repoServiceName = 'Bus';

    public function handleCommand(CommandInterface $command)
    {
        $bus = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $bus->updateServiceRegister(
            $command->getTrcConditionChecked(),
            $command->getTrcNotes(),
            $command->getCopiedToLaPte(),
            $command->getLaShortNote(),
            $command->getOpNotifiedLaPte(),
            $command->getApplicationSigned(),
            $command->getTimetableAcceptable(),
            $command->getMapSupplied(),
            $command->getRouteDescription()
        );

        $this->getRepo()->save($bus);

        $result = new Result();
        $result->addId('bus', $bus->getId());
        $result->addMessage('Bus registration saved successfully');

        return $result;
    }
}
