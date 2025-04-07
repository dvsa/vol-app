<?php

namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;

class VehiclesDeclarationsController extends Lva\AbstractVehiclesDeclarationsController
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected string $location = 'external';
}
