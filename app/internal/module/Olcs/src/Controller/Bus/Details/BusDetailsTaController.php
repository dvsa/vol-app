<?php

/**
 * Bus Details Ta Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Details;

/**
 * Bus Details Ta Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusDetailsTaController extends BusDetailsController
{
    protected $item = 'ta';

    public function indexAction()
    {
        $view = $this->getViewWithLicence();

        $view->setTemplate('licence/bus/index');
        return $this->renderView($view);
    }
}
