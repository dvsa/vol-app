<?php

/**
 * Internal Variation Business Type Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;

/**
 * Internal Variation Business Type Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class BusinessTypeController extends Lva\AbstractBusinessTypeController
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected $location = 'internal';
}
