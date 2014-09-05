<?php

/**
 * Bus Docs Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Docs;

use Olcs\Controller\Bus\BusController;

/**
 * Bus Docs Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusDocsController extends BusController
{
    protected $section = 'docs';

    public function indexAction()
    {
        $view = $this->getViewWithLicence();

        $view->setTemplate('licence/bus/index');
        return $this->viewVars($view, 'licence_bus_docs');
    }
}
