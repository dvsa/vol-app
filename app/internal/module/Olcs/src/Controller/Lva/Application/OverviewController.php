<?php

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva\AbstractController;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;
use Olcs\Controller\Lva\Traits\ApplicationOverviewTrait;

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class OverviewController extends AbstractController implements ApplicationControllerInterface
{
    use ApplicationControllerTrait,
        ApplicationOverviewTrait;

    protected $lva = 'application';
    protected $location = 'internal';
}
