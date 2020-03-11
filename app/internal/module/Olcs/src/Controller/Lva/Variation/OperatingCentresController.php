<?php

/**
 * Internal Operating Centres Variation Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use Common\Controller\Lva\Traits\VariationOperatingCentresControllerTrait;
use Olcs\Controller\Interfaces\VariationControllerInterface;

/**
 * Internal Operating Centres Variation Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class OperatingCentresController extends Lva\AbstractOperatingCentresController implements
    VariationControllerInterface
{
    use VariationControllerTrait,
        VariationOperatingCentresControllerTrait;

    protected $lva = 'variation';
    protected $location = 'internal';
}
