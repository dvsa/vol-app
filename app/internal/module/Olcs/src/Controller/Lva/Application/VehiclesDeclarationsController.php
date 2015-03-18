<?php

/**
 * Internal Application Vehicles Declarations Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * Internal Application Vehicles Declarations Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehiclesDeclarationsController extends Lva\AbstractVehiclesDeclarationsController implements
    ApplicationControllerInterface
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'internal';
}
