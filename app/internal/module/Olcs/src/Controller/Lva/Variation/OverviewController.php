<?php

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Common\Controller\Lva\AbstractController;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;
use Olcs\Controller\Lva\Traits\ApplicationTrackingTrait;

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OverviewController extends AbstractController
{
    use VariationControllerTrait,
        ApplicationTrackingTrait;

    protected $lva = 'variation';
    protected $location = 'internal';
}
