<?php

/**
 * Batch process queued licence status changes
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace Cli\Service\Processing;

use Common\Service\Entity\LicenceStatusRuleEntityService;
use Common\Service\Entity\LicenceEntityService;

/**
 * Batch process queued licence status changes
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class BatchLicenceStatusProcessingService extends AbstractBatchProcessingService
{
    /**
     * Process licence rule status changes to revoke, curtail and suspend
     *
     * @return void
     */
    public function processToRevokeCurtailSuspend()
    {
        $licenceStatusRuleService = $this->getServiceLocator()->get('Entity\LicenceStatusRule');
        $licenceStatusHelperService = $this->getServiceLocator()->get('Helper\LicenceStatus');
        $licenceService = $this->getServiceLocator()->get('Entity\Licence');
        $dateTime = $this->getServiceLocator()->get('Helper\Date')->getDate(\DateTime::W3C);

        $licencesToAction = $licenceStatusRuleService->getLicencesToRevokeCurtailSuspend();
        $this->outputLine(
            sprintf('%s %d rule(s) to process', __FUNCTION__, count($licencesToAction))
        );
        foreach ($licencesToAction as $row) {
            $this->outputLine(sprintf('=Processing rule id %d', $row['id']));

            // if licence is not valid, then continue
            if ($row['licence']['status']['id'] !== LicenceEntityService::LICENCE_STATUS_VALID) {
                $this->outputLine(
                    sprintf(
                        '==Licence id %d is not active, it is "%s"',
                        $row['licence']['id'],
                        $row['licence']['status']['description']
                    )
                );
                continue;
            }

            // update licence status
            $this->outputLine(
                sprintf('==Updating licence %d to status %s', $row['licence']['id'], $row['licenceStatus']['id'])
            );

            switch ($row['licenceStatus']['id']) {
                case LicenceEntityService::LICENCE_STATUS_CURTAILED:
                    $column = 'curtailedDate';
                    break;
                case LicenceEntityService::LICENCE_STATUS_REVOKED:
                    $column = 'revokedDate';
                    break;
                case LicenceEntityService::LICENCE_STATUS_SUSPENDED:
                    $column = 'suspendedDate';
                    break;
            }

            if ($row['licenceStatus']['id'] == LicenceStatusRuleEntityService::LICENCE_STATUS_RULE_REVOKED) {
                $terminateData = $licenceService->getRevocationDataForLicence($row['licence']['id']);

                $licenceStatusHelperService->ceaseDiscs($terminateData);
                $licenceStatusHelperService->removeLicenceVehicles($terminateData['licenceVehicles']);
                $licenceStatusHelperService->removeTransportManagers($terminateData['tmLicences']);
            }

            $licenceService->forceUpdate(
                $row['licence']['id'],
                [
                    'status' => $row['licenceStatus']['id'],
                    $column => $this->getServiceLocator()->get('Helper\Date')->getDate('Y-m-d H:i:s')
                ]
            );

            // update rule start processed date
            $this->outputLine(
                sprintf('==Updating licence rule %d to startProcessedDate %s', $row['id'], $dateTime)
            );
            $licenceStatusRuleService->forceUpdate($row['id'], ['startProcessedDate' => $dateTime]);
        }
    }

    /**
     * Process licence rule status back to valid
     *
     * @return void
     */
    public function processToValid()
    {
        $licenceStatusRuleService = $this->getServiceLocator()->get('Entity\LicenceStatusRule');
        $licenceService = $this->getServiceLocator()->get('Entity\Licence');
        $vehicleService = $this->getServiceLocator()->get('Entity\Vehicle');
        $dateTime = $this->getServiceLocator()->get('Helper\Date')->getDate(\DateTime::W3C);

        $licencesToAction = $licenceStatusRuleService->getLicencesToValid();
        $this->outputLine(
            sprintf('%s %d rule(s) to process', __FUNCTION__, count($licencesToAction))
        );
        foreach ($licencesToAction as $row) {
            $this->outputLine(sprintf('=Processing rule id %d', $row['id']), 1);

            // if licence is not curtailed or suspended then continue
            if (
                $row['licence']['status']['id'] !== LicenceEntityService::LICENCE_STATUS_CURTAILED &&
                $row['licence']['status']['id'] !== LicenceEntityService::LICENCE_STATUS_SUSPENDED
                ) {
                $this->outputLine(
                    sprintf(
                        '==Licence id %d is not curtailed or suspended, it is "%s"',
                        $row['licence']['id'],
                        $row['licence']['status']['description']
                    )
                );
                continue;
            }

            // update section26 on licenced vehicles
            foreach ($row['licence']['licenceVehicles'] as $licencedVehicle) {
                // check vehicle relationship is there (it is missing in test data)
                if ($licencedVehicle['vehicle']) {
                    $this->outputLine(
                        sprintf('==Updating vehicle %d set section26 = 0', $licencedVehicle['vehicle']['id'])
                    );
                    $vehicleService->forceUpdate($licencedVehicle['vehicle']['id'], ['section26' => 0]);
                }
            }

            // update licence status
            $this->outputLine(
                sprintf(
                    '==Updating licence %d to status %s',
                    $row['licence']['id'],
                    LicenceEntityService::LICENCE_STATUS_VALID
                )
            );

            $licenceService->forceUpdate(
                $row['licence']['id'],
                [
                    'status' => LicenceEntityService::LICENCE_STATUS_VALID,
                    'revokedDate' => null,
                    'curtailedDate' => null,
                    'suspendedDate' => null
                ]
            );

            // update rule start processed date
            $this->outputLine(
                sprintf('==Updating licence rule %d to endProcessedDate %s', $row['id'], $dateTime)
            );
            $licenceStatusRuleService->forceUpdate(
                $row['id'],
                [
                    'endProcessedDate' => $dateTime,
                ]
            );
        }
    }
}
