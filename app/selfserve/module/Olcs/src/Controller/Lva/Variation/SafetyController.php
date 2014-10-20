<?php

/**
 * External Variation Safety Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use Common\Controller\Lva\Traits\ApplicationSafetyControllerTrait;

/**
 * External Variation Safety Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SafetyController extends Lva\AbstractSafetyController
{
    use VariationControllerTrait,
        ApplicationSafetyControllerTrait;

    protected $lva = 'variation';
    protected $location = 'external';
}
