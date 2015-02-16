<?php

/**
 * Internal Application Vehicles Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Olcs\Controller\Lva\AbstractGenericVehiclesController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Common\Controller\Lva\Traits;

/**
 * Internal Application Vehicles Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehiclesController extends AbstractGenericVehiclesController
{
    use ApplicationControllerTrait,
        Traits\ApplicationGenericVehiclesControllerTrait,
        Traits\ApplicationGoodsVehiclesControllerTrait;

    protected $lva = 'application';
    protected $location = 'internal';
}
