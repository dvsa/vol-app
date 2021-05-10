<?php

/**
 * External Application Vehicles Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Dvsa\Olcs\Application\Controller;

use Common\Controller\Lva\AbstractGoodsVehiclesController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * External Application Vehicles Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehiclesController extends AbstractGoodsVehiclesController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'external';
}
