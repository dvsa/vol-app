<?php

/**
 * Schedule41.php
 */
namespace Olcs\BusinessService\Service\Lva;

use Common\BusinessService\BusinessServiceInterface;
use Common\BusinessService\Response;

use Common\Service\Entity\ConditionUndertakingEntityService;
use Zend\ServiceManager\ServiceLocatorAwareInterface;
use Zend\ServiceManager\ServiceLocatorAwareTrait;

/**
 * Class Schedule41
 *
 * Save down the schedule 4/1
 *
 * @package Olcs\BusinessService\Service\Lva
 */
class Schedule41 implements BusinessServiceInterface, ServiceLocatorAwareInterface
{
    use ServiceLocatorAwareTrait;

    public function process(array $params)
    {
        $operatingCentres = $params['data']['table']['id'];

        $s4EntityService = $this->getServiceLocator()->get('Entity\Schedule41');
        $s4Record = $s4EntityService->save(
            array(
                'application' => $params['winningApplication']['id'],
                'licence' => $params['losingLicence']['id'],
                'surrenderLicence' => $params['data']['surrenderLicence'],
                'receivedDate' => $this->getServiceLocator()->get('Helper\Date')->getDate(),
            )
        );

        $licenceOperatingCentreEntityService = $this->getServiceLocator()->get('Entity\LicenceOperatingCentre');
        foreach ($operatingCentres as $operatingCentre) {
            $licenceOperatingCentreEntityService->forceUpdate(
                $operatingCentre,
                array(
                    's4' => $s4Record['id']
                )
            );
        }

        $applicationOperatingCentreEntityService = $this->getServiceLocator()->get('Entity\ApplicationOperatingCentre');
        foreach ($params['losingLicence']['operatingCentres'] as $operatingCentre) {
            $applicationOperatingCentreEntityService->save(
                array(
                    'application' => $params['winningApplication']['id'],
                    'action' => 'A',
                    'adPlaced' => 'N',
                    'operatingCentre' => $operatingCentre['operating_centre_id'],
                    'noOfTrailersRequired' => $operatingCentre['noOfTrailersRequired'],
                    'noOfVehiclesRequired' => $operatingCentre['noOfVehiclesRequired'],
                    's4' => $s4Record['id']
                )
            );
        }

        $conditionUndertakingEntityService = $this->getServiceLocator()->get('Entity\ConditionUndertaking');
        foreach ($params['losingLicence']['operatingCentres'] as $operatingCentre) {
            foreach ($operatingCentre['operatingCentre']['conditionUndertakings'] as $condition) {
                $conditionUndertakingEntityService->save(
                    array(
                        'application' => $params['winningApplication']['id'],
                        'operatingCentre' => $operatingCentre['operatingCentre']['id'],
                        'conditionType' => $condition['condition_type'],
                        'addedVia' => ConditionUndertakingEntityService::ADDED_VIA_APPLICATION,
                        'action' => 'A',
                        'attachedTo' => ConditionUndertakingEntityService::ATTACHED_TO_OPERATING_CENTRE,
                        'isDraft' => 'Y',
                        'isFulfilled' => 'N',
                        's4' => $s4Record['id'],
                        'notes' => $condition['notes']
                    )
                );
            }
        }

        return new Response(Response::TYPE_SUCCESS);
    }
}
