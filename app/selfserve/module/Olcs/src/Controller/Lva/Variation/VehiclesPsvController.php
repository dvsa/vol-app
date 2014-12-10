<?php

/**
 * External Variation Vehicles PSV Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Olcs\Controller\Lva\AbstractGenericVehiclesPsvController;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use Common\Controller\Lva\Traits;

/**
 * External Variation Vehicles PSV Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class VehiclesPsvController extends AbstractGenericVehiclesPsvController
{
    use VariationControllerTrait,
        Traits\PsvLicenceControllerTrait,
        // @NOTE: AC says variations behave exactly as per licences, so...
        Traits\LicenceGenericVehiclesControllerTrait;

    protected $lva = 'variation';
    protected $location = 'external';
}
