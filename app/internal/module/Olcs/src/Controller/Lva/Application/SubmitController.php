<?php

/**
 * Application Submit Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Olcs\Controller\Lva\AbstractSubmitController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * Application Submit Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class SubmitController extends AbstractSubmitController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'internal';
}
