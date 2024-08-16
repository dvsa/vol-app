<?php

/**
 * CreateGracePeriod.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\GracePeriod;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Entity\Licence\GracePeriod;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * Create GracePeriod
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
final class CreateGracePeriod extends AbstractCommandHandler
{
    protected $repoServiceName = 'GracePeriod';

    protected $licenceRepo = null;

    public function handleCommand(CommandInterface $command)
    {
        $licence = $this->getRepo()
            ->getReference(Licence::class, $command->getLicence());

        $gracePeriod = new GracePeriod();
        $gracePeriod->setStartDate(new \DateTime($command->getStartDate()));
        $gracePeriod->setEndDate(new \DateTime($command->getEndDate()));
        $gracePeriod->setDescription($command->getDescription());

        $gracePeriod->setLicence($licence);

        $this->getRepo()->save($gracePeriod);

        $result = new Result();
        $result->addId('graceperiod', $gracePeriod->getId());
        $result->addMessage('Grace period created successfully');

        return $result;
    }
}
