<?php

/**
 * Bus Trc Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Trc;

use Olcs\Controller\Bus\BusController;

/**
 * Bus Trc Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusTrcController extends BusController
{
    protected $section = 'trc';

    public function indexAction()
    {
        $view = $this->getViewWithLicence();

        $view->setTemplate('licence/bus/index');
        return $this->viewVars($view, 'licence_bus_trc');
    }
}
