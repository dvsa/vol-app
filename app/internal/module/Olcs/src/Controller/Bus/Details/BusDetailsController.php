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

    public function indexAction()
    {
        $view = $this->getViewWithLicence();

        $view->setTemplate('licence/bus/index');
        return $this->viewVars($view, 'licence_bus_details');
    }


}
