<?php

/**
 * External Licence Vehicles PSV Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Olcs\Controller\Lva\AbstractGenericVehiclesPsvController;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use Common\Controller\Lva\Traits\LicenceGenericVehiclesControllerTrait;

/**
 * External Licence Vehicles PSV Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class VehiclesPsvController extends AbstractGenericVehiclesPsvController
{
    use LicenceControllerTrait,
        LicenceGenericVehiclesControllerTrait;

    protected $lva = 'licence';
    protected $location = 'external';
}
