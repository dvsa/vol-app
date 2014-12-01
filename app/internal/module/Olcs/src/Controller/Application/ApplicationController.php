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
        Traits\FeesActionTrait,
        Traits\ApplicationControllerTrait;

    /**
     * Shows fees table
     */
    public function feesAction()
    {
        $response = $this->checkActionRedirect('lva-application');
        if ($response) {
            return $response;
        }

        $licenceId = $this->getServiceLocator()
            ->get('Entity\Application')
            ->getLicenceIdForApplication(
                $this->params()->fromRoute('application')
            );

        return $this->commonFeesAction($licenceId);
    }

    /**
     * Placeholder stub
     *
     * @return ViewModel
     */
    public function caseAction()
    {
        $view = new ViewModel();
        $view->setTemplate('application/index');

        return $this->render($view);
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

        return $this->render($view);
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

        return $this->render($view);
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

        return $this->render($view);
    }

    public function grantAction()
    {
        $request = $this->getRequest();
        $id = $this->params('application');

        if ($request->isPost()) {

            if (!$this->isButtonPressed('cancel')) {

                $this->getServiceLocator()->get('Processing\Application')->processGrantApplication($id);

                $this->getServiceLocator()->get('Helper\FlashMessenger')
                    ->addSuccessMessage('The application was granted successfully');
            }

            return $this->redirect()->toRouteAjax('lva-application', array('application' => $id));
        }

        $form = $this->getServiceLocator()->get('Helper\Form')->createForm('GenericConfirmation');

        $view = new ViewModel(array('form' => $form));
        $view->setTemplate('application/grant');

        return $this->renderView($view, 'Grant application');
    }

    public function undoGrantAction()
    {
        $request = $this->getRequest();
        $id = $this->params('application');

        if ($request->isPost()) {

            if (!$this->isButtonPressed('cancel')) {

                $this->getServiceLocator()->get('Processing\Application')->processUnGrantApplication($id);

                $this->getServiceLocator()->get('Helper\FlashMessenger')
                    ->addSuccessMessage('The application grant has been undone successfully');
            }

            return $this->redirect()->toRouteAjax('lva-application', array('application' => $id));
        }

        $form = $this->getServiceLocator()->get('Helper\Form')->createForm('GenericConfirmation');

        $view = new ViewModel(array('form' => $form));
        $view->setTemplate('application/undo-grant');

        return $this->render($view);
    }

    protected function renderLayout($view)
    {
        return $this->render($view);
    }
}
