<?php

/**
 * Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

use Olcs\Controller\AbstractController;
use Zend\View\Model\ViewModel;
use Olcs\Controller\Traits;

/**
 * Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationController extends AbstractController
{
    use Traits\LicenceControllerTrait,
        Traits\FeesActionTrait;

    /**
     * Placeholder stub
     *
     * @return ViewModel
     */
    public function caseAction()
    {
        $view = new ViewModel();
        $view->setTemplate('application/index');

        $applicationJourneyHelper = $this->getServiceLocator()->get('ApplicationJourneyHelper');

        $applicationId = $this->params('application');

        return $applicationJourneyHelper->render($view, $applicationId);
    }

    /**
     * Placeholder stub
     *
     * @return ViewModel
     */
    public function environmentalAction()
    {
        $view = new ViewModel();
        $view->setTemplate('application/index');

        $applicationJourneyHelper = $this->getServiceLocator()->get('ApplicationJourneyHelper');

        $applicationId = $this->params('application');

        return $applicationJourneyHelper->render($view, $applicationId);
    }

    /**
     * Placeholder stub
     *
     * @return ViewModel
     */
    public function documentAction()
    {
        $view = new ViewModel();
        $view->setTemplate('application/index');

        $applicationJourneyHelper = $this->getServiceLocator()->get('ApplicationJourneyHelper');

        $applicationId = $this->params('application');

        return $applicationJourneyHelper->render($view, $applicationId);
    }

    /**
     * Placeholder stub
     *
     * @return ViewModel
     */
    public function processingAction()
    {
        $view = new ViewModel();
        $view->setTemplate('application/index');

        $applicationJourneyHelper = $this->getServiceLocator()->get('ApplicationJourneyHelper');

        $applicationId = $this->params('application');

        return $applicationJourneyHelper->render($view, $applicationId);
    }

    /**
     * Placeholder stub
     *
     * @return ViewModel
     */
    public function feeAction()
    {
        $view = new ViewModel();
        $view->setTemplate('application/index');

        $applicationJourneyHelper = $this->getServiceLocator()->get('ApplicationJourneyHelper');

        $applicationId = $this->params('application');

        return $applicationJourneyHelper->render($view, $applicationId);
    }
}
