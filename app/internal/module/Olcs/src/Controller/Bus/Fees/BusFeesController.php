<?php

/**
 * Bus Fees Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Fees;

use Olcs\Controller\Bus\BusController;

/**
 * Bus Fees Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusFeesController extends BusController
{
    protected $section = 'fees';
    protected $subNavRoute = 'licence_bus_fees';

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
