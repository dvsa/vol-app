<?php

/**
 * External Operating Centres Variation Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use Common\Controller\Lva\Traits\VariationOperatingCentresControllerTrait;

/**
 * External Operating Centres Variation Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class OperatingCentresController extends Lva\AbstractOperatingCentresController
{
    use VariationControllerTrait,
        VariationOperatingCentresControllerTrait;

    protected $lva = 'variation';
    protected $location = 'external';
}
