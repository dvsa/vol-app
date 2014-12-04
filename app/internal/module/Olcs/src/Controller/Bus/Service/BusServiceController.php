<?php

/**
 * Bus Service Controller
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
namespace Olcs\Controller\Bus\Service;

use Olcs\Controller\Bus\BusController;

/**
 * Bus Service Controller
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class BusServiceController extends BusController
{
    protected $section = 'service';
    protected $subNavRoute = 'licence_bus_service';

    protected $item = 'service';

    /* properties required by CrudAbstract */
    protected $formName = 'bus-register-service';

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
