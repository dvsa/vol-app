<?php

declare(strict_types=1);

namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva\AbstractVehiclesSizeController;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;

class VehiclesSizeController extends AbstractVehiclesSizeController
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected string $location = 'external';
}
