<?php

/**
 * External Application Vehicles Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Common\Controller\Lva\Traits;

/**
 * External Application Vehicles Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehiclesController extends Lva\AbstractVehiclesController
{
    use ApplicationControllerTrait,
        Traits\ApplicationVehiclesControllerTrait;

    protected $lva = 'application';
    protected $location = 'external';
}
