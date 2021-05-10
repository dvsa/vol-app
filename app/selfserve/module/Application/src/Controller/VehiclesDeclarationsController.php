<?php

/**
 * External Application Vehicles Declarations Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Application\Controller;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * External Application Vehicles Declarations Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehiclesDeclarationsController extends Lva\AbstractVehiclesDeclarationsController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'external';
}
