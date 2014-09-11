<?php

/**
 * Bus Details Quality Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace Olcs\Controller\Bus\Details;

/**
 * Bus Details Quality Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusDetailsQualityController extends BusDetailsController
{
    protected $item = 'quality';

    /**
     * Index action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $view = $this->getViewWithLicence();

        $view->setTemplate('licence/bus/index');
        return $this->renderView($view);
    }
}
