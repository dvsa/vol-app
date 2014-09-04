<?php

/**
 * Vehicle Controller
 */
namespace Olcs\Controller\Licence\Details;

use Common\Controller\Traits;

/**
 * Vehicle Controller
 */
class VehicleController extends AbstractLicenceDetailsController
{
    use Traits\VehicleSection;

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
     * We only want to show active vehicles
     *
     * @param array $licenceVehicle
     * @return boolean
     */
    protected function showVehicle($licenceVehicle)
    {
        if (empty($licenceVehicle['specifiedDate'])) {
            return false;
        }

        return true;
    }

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

        if ($action == 'add') {
            $data['licence-vehicle']['specifiedDate'] = date('Y-m-d');
        }

        return $this->doActionSave($data, $action);
    }
}
