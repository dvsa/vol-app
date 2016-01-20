<?php

/**
 * External Application Vehicles PSV Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva\AbstractVehiclesPsvController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * External Application Vehicles PSV Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehiclesPsvController extends AbstractVehiclesPsvController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'external';
}
