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
    protected $subNavRoute = 'licence_bus_route';

    /**
     * Index action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $view = $this->getViewWithBusReg();

        $view->setTemplate('pages/placeholder');
        return $this->renderView($view);
    }
}
