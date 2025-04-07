<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Application\Controller;

use Common\Controller\Lva\AbstractVehiclesSizeController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

class VehiclesSizeController extends AbstractVehiclesSizeController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected string $location  = 'external';
}
