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
     * @param array $vehicle
     * @return boolean
     */
    protected function showVehicle($vehicle)
    {
        if (empty($vehicle['specifiedDate'])) {
            return false;
        }

        return true;
    }
}
