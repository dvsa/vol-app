<?php

/**
 * Create Change Of Entity
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ChangeOfEntity;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Organisation\ChangeOfEntity as ChangeOfEntityEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Create Change Of Entity
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class CreateChangeOfEntity extends AbstractCommandHandler
{
    protected $repoServiceName = 'ChangeOfEntity';

    protected $extraRepos = ['Application'];

    public function handleCommand(CommandInterface $command)
    {
        $changeOfEntity = new ChangeOfEntityEntity();
        $application = $this->getRepo('Application')->fetchById($command->getApplicationId());

        $changeOfEntity
            ->setLicence($application->getLicence())
            ->setOldLicenceNo($command->getOldLicenceNo())
            ->setOldOrganisationName($command->getOldOrganisationName());

        $this->getRepo()->save($changeOfEntity);

        $result = new Result();
        $result->addId('changeOfEntity', $changeOfEntity->getId());
        $result->addMessage('ChangeOfEntity Created');

        return $result;
    }
}
