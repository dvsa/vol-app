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

    public function indexAction()
    {
        $view = $this->getViewWithLicence();

        $view->setTemplate('licence/bus/index');
        return $this->renderView($view);
    }
}
