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
use Zend\Form\Form;

/**
 * External Variation Vehicles PSV Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class VehiclesPsvController extends AbstractGenericVehiclesPsvController
{
    use VariationControllerTrait,
        Traits\PsvVariationControllerTrait,
        // @NOTE this at the moment just sets the application id of the licence vehicle
        Traits\ApplicationGenericVehiclesControllerTrait;

    protected $lva = 'variation';
    protected $location = 'external';
}
