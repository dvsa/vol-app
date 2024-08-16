<?php

/**
 * Inspection Request / Create From Grant
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\InspectionRequest;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\QueryHandler\Licence\EnforcementArea;
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
use Dvsa\Olcs\Api\Entity\EnforcementArea\EnforcementArea as EA;

/**
 * Inspection Request / Create From Grant
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class CreateFromGrant extends AbstractCommandHandler implements TransactionedInterface, AuthAwareInterface
{
    use AuthAwareTrait;

    protected $repoServiceName = 'InspectionRequest';

    protected $extraRepos = ['Application'];

    public function handleCommand(CommandInterface $command)
    {
        $result = new Result();

        /**
         * @var ApplicationEntity
         */
        $application = $this->getRepo('Application')->fetchWithLicenceAndOc($command->getApplication());

        $inspectionRequest = $this->createInspectionRequestObject($command, $application);
        $hasType = $inspectionRequest->getRequestType() === $this->getRepo()->getRefdataReference(InspectionRequestEntity::REQUEST_TYPE_NEW_OP);
        if ($hasType) {
            $this->getRepo()->save($inspectionRequest);
            $result->addId('inspectionRequest', $inspectionRequest->getId());
            $result->addMessage('Inspection request created successfully');

            $sendInspectionRequest = SendInspectionRequestCmd::create(
                [
                    'id' => $inspectionRequest->getId()
                ]
            );
            $result->merge($this->handleSideEffect($sendInspectionRequest));
        }

        return $result;
    }

    /**
     * Only create inspection request if the EA is not NI (EA-N)
     *
     * @param Cmd $command
     *
     * @return InspectionRequestEntity
     */
    private function createInspectionRequestObject(
        $command,
        $application
    ) {
        $inspectionRequest = new InspectionRequestEntity();
        $enforcementArea = $application->getLicence()->getEnforcementArea()->getId();
        if ($enforcementArea !== EA::NORTHERN_IRELAND_ENFORCEMENT_AREA_CODE) {
            $operatingCentres = $application->getOcForInspectionRequest();
            $operatingCentre = count($operatingCentres) ?
                $this->getRepo()->getReference(OperatingCentreEntity::class, $operatingCentres[0]->getId()) : null;
            $inspectionRequest->updateInspectionRequest(
                $this->getRepo()->getRefdataReference(InspectionRequestEntity::REQUEST_TYPE_NEW_OP),
                null,
                null,
                $command->getDuePeriod(),
                $this->getRepo()->getRefdataReference(InspectionRequestEntity::RESULT_TYPE_NEW),
                $command->getCaseworkerNotes(),
                $this->getRepo()->getRefdataReference(InspectionRequestEntity::REPORT_TYPE_MAINTENANCE_REQUEST),
                $this->getRepo()->getReference(ApplicationEntity::class, $command->getApplication()),
                $this->getRepo()->getReference(LicenceEntity::class, $application->getLicence()->getId()),
                $this->getCurrentUser(),
                $operatingCentre
            );
        }
        return $inspectionRequest;
    }
}
