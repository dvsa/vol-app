<?php

/**
 * External Variation Vehicles PSV Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva\AbstractVehiclesPsvController;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;

/**
 * External Variation Vehicles PSV Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class VehiclesPsvController extends AbstractVehiclesPsvController
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected $location = 'external';
}
