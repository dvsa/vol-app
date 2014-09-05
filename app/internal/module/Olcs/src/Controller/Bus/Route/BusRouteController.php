<?php

/**
 * Bus Route Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Route;

use Olcs\Controller\Bus\BusController;

/**
 * Bus Route Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusRouteController extends BusController
{
    protected $section = 'route';

    public function indexAction()
    {
        $view = $this->getViewWithLicence();

        $view->setTemplate('licence/bus/index');
        return $this->viewVars($view, 'licence_bus_route');
    }
}
