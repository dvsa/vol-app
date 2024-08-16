<?php

/**
 * Inspection Request / Create
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\InspectionRequest;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Inspection\InspectionRequest as InspectionRequestEntity;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre as OperatingCentreEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Transfer\Command\InspectionRequest\CreateFromGrant as Cmd;
use Dvsa\Olcs\Api\Domain\Command\InspectionRequest\SendInspectionRequest as SendInspectionRequestCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;

/**
 * Inspection Request / Create
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class Create extends AbstractCommandHandler implements TransactionedInterface, AuthAwareInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'InspectionRequest';

    protected $extraRepos = ['Application', 'Licence'];

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        $inspectionRequest = $this->createInspectionRequestObject($command);
        $this->getRepo()->save($inspectionRequest);

        $result->addId('inspectionRequest', $inspectionRequest->getId());
        $result->addMessage('Inspection request created successfully');

        $sendInspectionRequest = SendInspectionRequestCmd::create(
            [
                'id' => $inspectionRequest->getId()
            ]
        );
        if ($command->getReportType() === InspectionRequestEntity::REPORT_TYPE_MAINTENANCE_REQUEST) {
            $result->merge($this->handleSideEffect($sendInspectionRequest));
        }

        return $result;
    }

    /**
     * @param Cmd $command
     * @return InspectionRequestEntity
     */
    private function createInspectionRequestObject($command)
    {
        $inspectionRequest = new InspectionRequestEntity();

        if ($command->getType() === 'application') {
            $application = $this->getRepo('Application')->fetchWithLicence($command->getApplication());
            $applicationId = $application->getId();
            $licenceId = $application->getLicence()->getId();
            $appReference = $this->getRepo()->getReference(ApplicationEntity::class, $applicationId);
        } else {
            $licence = $this->getRepo('Licence')->fetchById($command->getLicence());
            $licenceId = $licence->getId();
            $appReference = null;
        }
        $licReference = $this->getRepo()->getReference(LicenceEntity::class, $licenceId);

        $inspectionRequest->updateInspectionRequest(
            $this->getRepo()->getRefdataReference($command->getRequestType()),
            $command->getRequestDate(),
            $command->getDueDate(),
            null,
            $this->getRepo()->getRefdataReference($command->getResultType()),
            $command->getRequestorNotes(),
            $this->getRepo()->getRefdataReference($command->getReportType()),
            $appReference,
            $licReference,
            $this->getCurrentUser(),
            $this->getRepo()->getReference(OperatingCentreEntity::class, $command->getOperatingCentre()),
            $command->getInspectorName(),
            $command->getReturnDate(),
            $command->getFromDate(),
            $command->getToDate(),
            $command->getVehiclesExaminedNo(),
            $command->getTrailersExaminedNo(),
            $command->getInspectorNotes()
        );
        return $inspectionRequest;
    }
}
