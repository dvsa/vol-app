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
     * Holds the action table name
     *
     * @var string
     */
    protected $actionTableName = 'application_vehicle-safety_vehicle-history';

    /**
     * Setup the section
     *
     * @var string
     */
    protected $section = 'vehicle';

    /**
     * This section uses a flipped section, where the tables comes after.
     *
     * @var string
     */
    protected $viewTemplateName = 'partials/section-flipped';

    /**
     * Save the vehicle
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

    protected function parentActionSave($data, $service = null)
    {
        return parent::actionSave($data, $service);
    }
}
