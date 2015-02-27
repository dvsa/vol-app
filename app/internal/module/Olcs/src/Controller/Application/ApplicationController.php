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
use Common\Service\Entity\ApplicationEntityService;

/**
 * Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationController extends AbstractController
{
    protected $headerViewTemplate = 'partials/application-header.phtml';
    protected $pageLayout = 'application-section';

    use Traits\LicenceControllerTrait,
        Traits\FeesActionTrait,
        Traits\DocumentSearchTrait,
        Traits\DocumentActionTrait,
        Traits\ApplicationControllerTrait;

    /**
     * Route (prefix) for fees action redirects
     * @see Olcs\Controller\Traits\FeesActionTrait
     * @return string
     */
    protected function getFeesRoute()
    {
        return 'lva-application/fees';
    }

    /**
     * The fees route redirect params
     * @see Olcs\Controller\Traits\FeesActionTrait
     * @return array
     */
    protected function getFeesRouteParams()
    {
        return [
            'application' => $this->getFromRoute('application')
        ];
    }

    /**
     * The controller specific fees table params
     * @see Olcs\Controller\Traits\FeesActionTrait
     * @return array
     */
    protected function getFeesTableParams()
    {
        return [
            'licence' => $this->getLicenceIdForApplication()
        ];
    }

    public function payFeesAction()
    {
        $licenceId = $this->getServiceLocator()
            ->get('Entity\Application')
            ->getLicenceIdForApplication(
                $this->params('application')
            );

        $this->pageLayout = null;

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

        $params = array_merge(
            $params,
            $this->getRequest()->getQuery()->toArray(),
            array('query' => $this->getRequest()->getQuery())
        );

        $results = $this->getServiceLocator()
            ->get('DataServiceManager')
            ->get('Olcs\Service\Data\Cases')->fetchList($params);

        $view = new ViewModel(['table' => $this->getTable('case', $results, $params)]);
        $view->setTemplate('partials/table');

        $this->loadScripts(['table-actions']);

        return $this->render($view);
    }

    public function setRequest(\Zend\Http\Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Placeholder stub
     *
     * @return ViewModel
     */
    public function environmentalAction()
    {
        $view = new ViewModel();
        $view->setTemplate('pages/placeholder');

        return $this->render($view);
    }

    public function grantAction()
    {
        $request = $this->getRequest();
        $id = $this->params('application');

        if ($request->isPost()) {

            $applicationType = $this->getServiceLocator()->get('Entity\Application')->getApplicationType($id);

            if (!$this->isButtonPressed('cancel')) {

                if ($applicationType === ApplicationEntityService::APPLICATION_TYPE_NEW) {
                    $this->getServiceLocator()->get('Processing\Application')->processGrantApplication($id);
                } else {
                    $this->getServiceLocator()->get('Processing\Application')->processGrantVariation($id);
                }

                $this->getServiceLocator()->get('Helper\FlashMessenger')
                    ->addSuccessMessage('application-granted-successfully');
            }

            if ($applicationType === ApplicationEntityService::APPLICATION_TYPE_NEW) {
                return $this->redirect()->toRouteAjax('lva-application', array('application' => $id));
            } else {
                return $this->redirect()->toRouteAjax('lva-variation', array('application' => $id));
            }
        }

        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $form = $formHelper->createForm('GenericConfirmation');

        $form->get('messages')->get('message')->setValue('confirm-grant-application');

        $formHelper->setFormActionFromRequest($form, $request);

        $this->pageLayout = null;

        $view = new ViewModel(array('form' => $form));
        $view->setTemplate('partials/form');

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

        $form->get('messages')->get('message')->setValue('confirm-undo-grant-application');

        $formHelper->setFormActionFromRequest($form, $request);

        $this->pageLayout = null;

        $view = new ViewModel(array('form' => $form));
        $view->setTemplate('partials/form');

        return $this->renderView($view, 'Undo grant application');
    }

    protected function renderLayout($view)
    {
        return $this->render($view);
    }

    protected function getLicenceIdForApplication($applicationId = null)
    {
        if (is_null($applicationId)) {
            $applicationId = $this->params()->fromRoute('application');
        }
        return $this->getServiceLocator()
            ->get('Entity\Application')
            ->getLicenceIdForApplication($applicationId);
    }

    /**
     * Route (prefix) for document action redirects
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return string
     */
    protected function getDocumentRoute()
    {
        return 'lva-application/documents';
    }

    /**
     * Route params for document action redirects
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return array
     */
    protected function getDocumentRouteParams()
    {
        return array(
            'application' => $this->getFromRoute('application')
        );
    }

    /**
     * Get view model for document action
     * @see Olcs\Controller\Traits\DocumentActionTrait
     * @return ViewModel
     */
    protected function getDocumentView()
    {
        $applicationId = $this->getFromRoute('application');
        $licenceId = $this->getLicenceIdForApplication($applicationId);

        $filters = $this->mapDocumentFilters(
            array('licenceId' => $licenceId)
        );

        $table = $this->getDocumentsTable($filters);
        $form  = $this->getDocumentForm($filters);

        return $this->getViewWithApplication(
            array(
                'table' => $table,
                'form'  => $form
            )
        );
    }
}
