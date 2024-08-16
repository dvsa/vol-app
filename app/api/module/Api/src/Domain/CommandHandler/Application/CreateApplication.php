<?php

/**
 * Create Application
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Application\CreateApplicationFee as CreateAplicationFeeCommand;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\Traits\DerivedTypeOfLicenceParamsTrait;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\SlaCalculatorAwareInterface;
use Dvsa\Olcs\Api\Domain\SlaCalculatorAwareTrait;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion;
use Dvsa\Olcs\Api\Entity\Application\ApplicationTracking;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Transfer\Command\Application\CreateApplication as Cmd;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Domain\Command\Application\GenerateLicenceNumber;

/**
 * Create Application
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CreateApplication extends AbstractCommandHandler implements AuthAwareInterface, TransactionedInterface, SlaCalculatorAwareInterface
{
    use AuthAwareTrait;
    use DerivedTypeOfLicenceParamsTrait;
    use SlaCalculatorAwareTrait;

    protected $repoServiceName = 'Application';

    protected $extraRepos = ['Sla'];

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $licence = $this->createLicenceObject($command);
        $result->addMessage('Licence created');

        $application = $this->createApplicationObject($command, $licence);
        $result->addMessage('Application created');

        $updatedTol = $this->populateTypeOfLicence($command, $application);

        $this->createApplicationCompletion($application);
        $result->addMessage('Application Completion created');

        $this->createApplicationTracking($application);
        $result->addMessage('Application Tracking created');

        $this->getRepo()->save($application);

        $result->addId('application', $application->getId());
        $result->addId('licence', $licence->getId());

        if ($updatedTol) {
            $result->merge($this->createApplicationFee($application->getId()));
            $result->merge($this->updateApplicationCompletion($application->getId()));
            if ($application->getLicence()->getTrafficArea() !== null) {
                $result->merge($this->generateLicenceNumber($application->getId()));
            }
        }

        return $result;
    }

    private function populateTypeOfLicence(Cmd $command, Application $application)
    {
        $commandNiFlag = $command->getNiFlag();

        if ($commandNiFlag !== null && $command->getLicenceType() !== null) {
            $derivedOperatorType = $this->getDerivedOperatorType(
                $command->getOperatorType(),
                $commandNiFlag
            );

            $derivedVehicleType = $this->getDerivedVehicleType(
                $command->getVehicleType(),
                $derivedOperatorType
            );

            $application->updateTypeOfLicence(
                $commandNiFlag,
                $this->getRepo()->getRefdataReference($derivedOperatorType),
                $this->getRepo()->getRefdataReference($command->getLicenceType()),
                $this->getRepo()->getRefdataReference($derivedVehicleType),
                $command->getLgvDeclarationConfirmation() ?? 0
            );

            return true;
        }

        return false;
    }

    private function updateApplicationCompletion($applicationId)
    {
        if ($this->isInternalUser()) {
            $this->handleSideEffect(
                UpdateApplicationCompletion::create(['id' => $applicationId, 'section' => 'businessType'])
            );
        }
        return $this->handleSideEffect(
            UpdateApplicationCompletion::create(['id' => $applicationId, 'section' => 'typeOfLicence'])
        );
    }

    /**
     * Generate licence number
     *
     * @param int $applicationId
     * @return Result
     */
    private function generateLicenceNumber($applicationId)
    {
        return $this->handleSideEffect(
            GenerateLicenceNumber::create(['id' => $applicationId])
        );
    }

    private function createApplicationFee($applicationId)
    {
        return $this->handleSideEffect(CreateAplicationFeeCommand::create(['id' => $applicationId]));
    }

    private function createApplicationCompletion(Application $application)
    {
        $applicationCompletion = new ApplicationCompletion($application);

        $application->setApplicationCompletion($applicationCompletion);
    }

    private function createApplicationTracking(Application $application)
    {
        $applicationTracking = new ApplicationTracking($application);

        $application->setApplicationTracking($applicationTracking);
    }

    /**
     * @return Licence
     */
    private function createLicenceObject(Cmd $command)
    {
        $organisation = $this->getRepo()->getReference(Organisation::class, $command->getOrganisation());
        if ($this->isGranted(Permission::INTERNAL_USER)) {
            $status = $this->getRepo()->getRefdataReference(Licence::LICENCE_STATUS_UNDER_CONSIDERATION);
        }
        if ($this->isGranted(Permission::SELFSERVE_USER)) {
            $status = $this->getRepo()->getRefdataReference(Licence::LICENCE_STATUS_NOT_SUBMITTED);
        }

        $licence = new Licence($organisation, $status);

        if ($command->getTrafficArea() !== null) {
            $licence->setTrafficArea(
                $this->getRepo()->getReference(TrafficArea::class, $command->getTrafficArea())
            );
        }

        return $licence;
    }

    /**
     * @return Application
     */
    private function createApplicationObject(Cmd $command, Licence $licence)
    {
        $application = new Application($licence, $this->getApplicationStatus(), false);

        if ($command->getReceivedDate() !== null) {
            $receivedDate = new \DateTime($command->getReceivedDate());
            $application->setReceivedDate($receivedDate);
            $this->setTargetCompletionDateForApplication($application);
        }
        if ($this->isGranted(Permission::SELFSERVE_USER)) {
            $application->setAppliedVia($this->getRepo()->getRefdataReference(Application::APPLIED_VIA_SELFSERVE));
        }
        if ($this->isGranted(Permission::INTERNAL_USER)) {
            $application->setAppliedVia($this->getRepo()->getRefdataReference($command->getAppliedVia()));
        }

        return $application;
    }

    private function getApplicationStatus()
    {
        if ($this->isGranted(Permission::INTERNAL_USER)) {
            return $this->getRepo()->getRefdataReference(Application::APPLICATION_STATUS_UNDER_CONSIDERATION);
        }

        return $this->getRepo()->getRefdataReference(Application::APPLICATION_STATUS_NOT_SUBMITTED);
    }
}
