<?php

/**
 * Update Change Of Entity
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ChangeOfEntity;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Organisation\ChangeOfEntity as ChangeOfEntityEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Update Change Of Entity
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class UpdateChangeOfEntity extends AbstractCommandHandler
{
    protected $repoServiceName = 'ChangeOfEntity';

    public function handleCommand(CommandInterface $command)
    {
        /* @var $changeOfEntity ChangeOfEntityEntity */
        $changeOfEntity = $this->getRepo()->fetchUsingId($command);

        $changeOfEntity
            ->setOldLicenceNo($command->getOldLicenceNo())
            ->setOldOrganisationName($command->getOldOrganisationName());

        $this->getRepo()->save($changeOfEntity);

        $result = new Result();
        $result->addId('changeOfEntity', $changeOfEntity->getId());
        $result->addMessage('ChangeOfEntity Updated');

        return $result;
    }
}
