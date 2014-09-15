<?php

/**
 * Bus Details Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Details;

use Olcs\Controller\Bus\BusController;

/**
 * Bus Details Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusDetailsController extends BusController
{
    protected $section = 'details';
    protected $subNavRoute = 'licence_bus_details';

    /**
     * Index action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $view = $this->getViewWithBusReg();

        $view->setTemplate('licence/bus/index');
        return $this->renderView($view);
    }
}
