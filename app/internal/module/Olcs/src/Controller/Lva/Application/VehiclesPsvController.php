<?php

/**
 * Internal Application Vehicles PSV Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva\AbstractVehiclesPsvController;
use Common\Controller\Lva\Traits;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * Internal Application Vehicles PSV Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class VehiclesPsvController extends AbstractVehiclesPsvController
{
    use ApplicationControllerTrait,
        Traits\ApplicationGenericVehiclesControllerTrait;

    protected $lva = 'application';
    protected $location = 'internal';

    /**
     * Whether to display the vehicle
     *
     * @param array $licenceVehicle
     * @param array $filters
     * @return boolean
     */
    protected function showVehicle(array $licenceVehicle, array $filters = [])
    {
        return empty($licenceVehicle['removalDate']);
    }
}
