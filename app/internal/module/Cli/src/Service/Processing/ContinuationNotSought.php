<?php

/**
 * Change the status to CNS for operator licences where the continuation fee has not been paid
 * and the continuation date is in the past.
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace Cli\Service\Processing;

use Common\BusinessService\Response;

/**
 * Change the status to CNS for operator licences where the continuation fee has not been paid
 * and the continuation date is in the past.
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ContinuationNotSought extends AbstractBatchProcessingService
{
    /**
     * Change the status to CNS for operator licences where the continuation fee has not been paid
     * and the continuation date is in the past.
     *
     * @param array $params No params required
     * @return Common\BusinessService\ResponseInterface
     */
    public function process(array $params)
    {
        $dryRun = isset($params['dryRun']) && $params['dryRun'];

        $licenceStatusHelper = $this->getServiceLocator()->get('Helper\LicenceStatus');
        $licenceEntityService = $this->getServiceLocator()->get('Entity\Licence');
        $tmLicenceEntityService = $this->getServiceLocator()->get('Entity\TransportManagerLicence');
        $applicationProcessingService = $this->getServiceLocator()->get('Processing\Application');

        $result = $licenceEntityService->getForContinuationNotSought();

        $this->outputLine("{$result['Count']} Licence(s) found to change to CNS");

        foreach ($result['Results'] as $licenceData) {
            $this->outputLine("Processing Licence ID {$licenceData['id']}");

            if (!$dryRun) {
                $this->outputLine("= Set status to CNS");
                $licenceEntityService->setStatusToContinuationNotSought($licenceData);

                $this->outputLine("= Void any discs associated to vehicles linked to the licence");
                $licenceStatusHelper->ceaseDiscs($licenceData);

                $this->outputLine("= Remove any vehicles");
                $licenceStatusHelper->removeLicenceVehicles($licenceData['licenceVehicles']);

                $this->outputLine("= Unlink any Transport Managers");
                $tmLicenceEntityService->deleteForLicence($licenceData['id']);

                $this->outputLine("= Expire community licences that are of status 'Pending', 'Active' or 'Suspended'");
                $applicationProcessingService->expireCommunityLicencesForLicence($licenceData['id']);
            }
        }
    }
}
