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
    protected $subNavRoute = 'licence_bus_trc';

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
