<?php

/**
 * Vehicle Controller
 */
namespace Olcs\Controller\Licence\Details;

use Common\Controller\Traits\VehicleSection;

/**
 * Vehicle Controller
 */
class VehicleController extends AbstractLicenceDetailsController
{
    use VehicleSection;

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
}
