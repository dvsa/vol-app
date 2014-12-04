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
        Traits\DocumentSearchTrait,
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

        $licenceId = $this->getLicenceIdForApplication();

        return $this->commonFeesAction($licenceId);
    }

    public function payFeesAction()
    {
        $licenceId = $this->getServiceLocator()
            ->get('Entity\Application')
            ->getLicenceIdForApplication(
                $this->params('application')
            );

        return $this->commonPayFeesAction('lva-application', $licenceId);
    }

    /**
     * Placeholder stub
     *
     * @return ViewModel
     */
    public function caseAction()
    {
        $this->checkForCrudAction('case', [], 'case');

        $params = [
            'application' => $this->params()->fromRoute('application', null),
            'page'    => $this->params()->fromRoute('page', 1),
            'sort'    => $this->params()->fromRoute('sort', 'id'),
            'order'   => $this->params()->fromRoute('order', 'desc'),
            'limit'   => $this->params()->fromRoute('limit', 10),
        ];

        $results = $this->getServiceLocator()
            ->get('DataServiceManager')
            ->get('Olcs\Service\Data\Cases')->fetchList($params);

        $view = new ViewModel(['table' => $this->getTable('case', $results, $params)]);
        $view->setTemplate('licence/cases');

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

    // @TODO DocumentActionTrait? shared with LicenceController
    public function documentsAction()
    {
        // if ($this->getRequest()->isPost()) {
        //     $action = strtolower($this->params()->fromPost('action'));

        //     if ($action === 'new letter') {
        //         $action = 'generate';
        //     }

        //     $params = [
        //         'licence' => $this->getFromRoute('licence')
        //     ];

        //     return $this->redirect()->toRoute(
        //         'licence/documents/'.$action,
        //         $params
        //     );
        // }

        $this->pageLayout = 'application';

        $applicationId = $this->params()->fromRoute('application');
        $licenceId = $this->getLicenceIdForApplication($applicationId);

        $filters = $this->mapDocumentFilters(
            array('licenceId' => $licenceId)
        );

        $view = $this->getViewWithApplication(
            array(
                'table' => $this->getDocumentsTable($filters),
                'form'  => $this->getDocumentForm($filters)
            )
        );

        $this->loadScripts(['documents', 'table-actions']);

        $view->setTemplate('licence/docs-attachments');
        $view->setTerminal(
            $this->getRequest()->isXmlHttpRequest()
        );

//        return $this->renderLayout($view);
        return $this->renderView($view);
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

        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $form = $formHelper->createForm('GenericConfirmation');

        $formHelper->setFormActionFromRequest($form, $request);

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

        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $form = $formHelper->createForm('GenericConfirmation');

        $formHelper->setFormActionFromRequest($form, $request);

        $view = new ViewModel(array('form' => $form));
        $view->setTemplate('application/undo-grant');

        return $this->renderView($view, 'Undo grant application');
    }

    protected function renderLayout($view)
    {
        return $this->render($view);
    }

    protected function getLicenceIdForApplication($applicationId = null) {
        if (is_null($applicationId)) {
            $applicationId = $this->params()->fromRoute('application');
        }
        return $this->getServiceLocator()
            ->get('Entity\Application')
            ->getLicenceIdForApplication($applicationId);
    }
}
