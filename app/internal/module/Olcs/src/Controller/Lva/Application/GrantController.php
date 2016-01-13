<?php

/**
 * Application Grant Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Olcs\Controller\Lva\AbstractGrantController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * Application Grant Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class GrantController extends AbstractGrantController implements ApplicationControllerInterface
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'internal';
}
