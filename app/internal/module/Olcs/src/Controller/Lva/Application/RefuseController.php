<?php

/**
 * Application Refuse Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller\Lva\Application;

use Olcs\Controller\Lva\AbstractRefuseController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * Application Refuse Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class RefuseController extends AbstractRefuseController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'internal';
}
