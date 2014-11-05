<?php

/**
 * Internal Variation Vehicles PSV Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva\AbstractVehiclesPsvController;
use Common\Controller\Lva\Traits;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;

/**
 * Internal Variation Vehicles PSV Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class VehiclesPsvController extends AbstractVehiclesPsvController
{
    use VariationControllerTrait,
        // @NOTE: AC says variations behave exactly as per licences, so...
        Traits\LicenceGenericVehiclesControllerTrait;

    protected $lva = 'variation';
    protected $location = 'internal';
}
