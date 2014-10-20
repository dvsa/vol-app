<?php

/**
 * External Variation Business Details Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva\AbstractBusinessDetailsController;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;

/**
 * External Variation Business Details Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class BusinessDetailsController extends AbstractBusinessDetailsController
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected $location = 'external';
}
