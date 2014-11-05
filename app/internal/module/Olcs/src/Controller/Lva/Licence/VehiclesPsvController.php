<?php

/**
 * Internal Licence Vehicles PSV Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Common\Controller\Lva\AbstractVehiclesPsvController;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use Common\Controller\Lva\Traits;

/**
 * Internal Licence Vehicles PSV Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class VehiclesPsvController extends AbstractVehiclesPsvController
{
    use LicenceControllerTrait,
        Traits\LicenceGenericVehiclesControllerTrait;

    protected $lva = 'licence';
    protected $location = 'internal';
}
