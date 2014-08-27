<?php

/**
 * Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Application;

use Olcs\Controller\AbstractController;
use Zend\View\Model\ViewModel;

/**
 * Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationController extends AbstractController
{
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

        $applicationId = $this->params()->fromRoute('applicationId');

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

        $applicationId = $this->params()->fromRoute('applicationId');

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

        $applicationId = $this->params()->fromRoute('applicationId');

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

        $applicationId = $this->params()->fromRoute('applicationId');

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

        $applicationId = $this->params()->fromRoute('applicationId');

        return $applicationJourneyHelper->render($view, $applicationId);
    }
}
