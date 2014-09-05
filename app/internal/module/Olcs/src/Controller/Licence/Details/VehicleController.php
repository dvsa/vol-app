<?php

/**
 * Vehicle Controller
 *
 * Internal - Licence - Vehicle section
 */
namespace Olcs\Controller\Licence\Details;

use Common\Controller\Traits\VehicleSafety as VehicleSafetyTraits;

/**
 * Vehicle Controller
 */
class VehicleController extends AbstractLicenceDetailsController
{
    use VehicleSafetyTraits\VehicleSection,
        VehicleSafetyTraits\InternalGenericVehicleSection,
        VehicleSafetyTraits\LicenceGenericVehicleSection;

    /**
     * Set the form name
     *
     * @var string
     */
    protected $formName = 'application_vehicle-safety_vehicle';

    /**
     * Holds the table name
     *
     * @var string
     */
    protected $tableName = 'application_vehicle-safety_vehicle';

    /**
     * Setup the section
     *
     * @var string
     */
    protected $section = 'vehicle';

    /**
     * Save the vehicle
     *
     * @todo might be able to combine these 2 methods now
     *
     * @param array $data
     * @param string $service
     */
    protected function actionSave($data, $service = null)
    {
        $action = $this->getActionName();

        $licenceVehicleSaved = $this->internalActionSave($data, $action);

        if ($action == 'add' && isset($licenceVehicleSaved['id'])) {
            $this->requestDisc($licenceVehicleSaved['id']);
        }
    }

    /**
     * Request disc
     *
     * @param int $licenceVehicleId
     */
    protected function requestDisc($licenceVehicleId)
    {
        $this->makeRestCall('GoodsDisc', 'POST', array('licenceVehicle' => $licenceVehicleId));
    }

    /**
     * Get total number of vehicles
     *
     * @return int
     */
    protected function getTotalNumberOfVehicles()
    {
        $bundle = array(
            'properties' => array(),
            'children' => array(
                'licenceVehicles' => array(
                    'properties' => array('id')
                )
            )
        );

        $data = $this->makeRestCall('Licence', 'GET', array('id' => $this->getLicenceId()), $bundle);

        return count($data['licenceVehicles']);
    }
}
