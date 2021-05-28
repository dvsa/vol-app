<?php

namespace Dvsa\Olcs\Application\Controller;

use Common\Controller\Lva\AbstractGoodsVehiclesController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * @deprecated Being replaced with new controllers
 */
class LvaVehicleController extends AbstractGoodsVehiclesController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'external';
}
