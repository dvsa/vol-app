<?php

/**
 * Bus Details Stop Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Details;

/**
 * Bus Details Stop Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusDetailsStopController extends BusDetailsController
{
    protected $item = 'stop';

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
