<?php

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva\AbstractController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Olcs\Controller\Lva\Traits\ApplicationTrackingTrait;

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class OverviewController extends AbstractController
{
    use ApplicationControllerTrait,
        ApplicationTrackingTrait;

    protected $lva = 'application';
    protected $location = 'internal';
}
