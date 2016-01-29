<?php

/**
 * ApplicationSchedule41Controller.php
 */
namespace Olcs\Controller\Application;

use Dvsa\Olcs\Transfer\Query\Application\Application;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

use Common\Controller\Lva\Schedule41Controller;

use Common\Controller\Lva\AbstractController;
use Common\Controller\Plugin\Redirect;

/**
 * Class ApplicationSchedule41Controller
 *
 * Application41 schedule controller.
 *
 * @package Olcs\Controller\Application
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class ApplicationSchedule41Controller extends Schedule41Controller implements ApplicationControllerInterface
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'internal';

    protected $section = 'operating_centres';
}
