<?php

/**
 * External Application Transport Managers Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Common\Controller\Lva;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * External Application Transport Managers Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TransportManagersController extends Lva\AbstractTransportManagersController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'external';

    /**
     * Render place holder page
     *
     * @return \Zend\View\Model\ViewModel
     */
    private function renderPlaceHolder()
    {
        $view = new \Zend\View\Model\ViewModel();
        $view->setTemplate('pages/placeholder');

        return $this->renderView($view);
    }

     /**
     * Details page
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function detailsAction()
    {
        return $this->renderPlaceHolder();
    }

    /**
     * Awaiting signature page
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function awaitingSignatureAction()
    {
        return $this->renderPlaceHolder();
    }

    /**
     * TM signed page
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function tmSignedAction()
    {
        return $this->renderPlaceHolder();
    }

    /**
     * Operator signed page
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function operatorSignedAction()
    {
        return $this->renderPlaceHolder();
    }

    /**
     * Post Application page
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function postalApplicationAction()
    {
        return $this->renderPlaceHolder();
    }
}
