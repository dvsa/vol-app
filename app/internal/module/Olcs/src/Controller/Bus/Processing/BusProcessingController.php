<?php

/**
 * Bus Processing Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Processing;

use Olcs\Controller\Bus\BusController;

/**
 * Bus Processing Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusProcessingController extends BusController
{
    protected $section = 'processing';
    protected $subNavRoute = 'licence_bus_processing';

    /**
     * Index action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $view = $this->getViewWithBusReg();

        $view->setTemplate('view-new/pages/placeholder');
        return $this->renderView($view);
    }
}
