<?php

/**
 * Overview
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Application\ApplicationTracking as ApplicationTrackingEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea as TrafficAreaEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Vehicle\DeleteLicenceVehicle as VehicleCmd;

/**
 * Overview
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
final class Overview extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'Application';

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /* @var $application ApplicationEntity */
        $application = $this->getRepo()->fetchUsingId($command, Query::HYDRATE_OBJECT, $command->getVersion());

        $this->setTrackingData($application, $command);

        $this->setLeadTcArea($application, $command);

        if (!is_null($command->getReceivedDate())) {
            $application->setReceivedDate(new \DateTime($command->getReceivedDate()));
        }
        if (!is_null($command->getTargetCompletionDate())) {
            $application->setTargetCompletionDate(new \DateTime($command->getTargetCompletionDate()));
        }

        $application->setOverrideOoo($command->getOverrideOppositionDate());

        $application->setApplicationReferredToPi($command->getApplicationReferredToPi());

        $this->getRepo()->save($application);

        $result
            ->addId('application', $application->getId())
            ->addId('applicationTracking', $application->getApplicationTracking()->getId())
            ->addMessage('Application updated');

        return $result;
    }

    protected function setTrackingData(ApplicationEntity $application, CommandInterface $command)
    {
        /** @var ApplicationTrackingEntity $application */
        $tracking = $application->getApplicationTracking();

        if (is_null($tracking)) {
            $tracking = new ApplicationTrackingEntity($application);
            $application->setApplicationTracking($tracking);
        }

        $trackingData = $command->getTracking();
        $tracking
            ->setId($trackingData['id'])
            ->setVersion($trackingData['version'])
            ->exchangeStatusArray($trackingData);
    }

    protected function setLeadTcArea(ApplicationEntity $application, CommandInterface $command)
    {
        if (!is_null($command->getLeadTcArea())) {
            /** @var OrganisationEntity $organisation */
            $organisation = $application->getLicence()->getOrganisation();
            $organisation->setLeadTcArea(
                $this->getRepo()->getReference(TrafficAreaEntity::class, $command->getLeadTcArea())
            );
        }
    }
}
