<?php

namespace Olcs\Controller\Application;

use Common\Controller\Traits\CheckForCrudAction;
use Common\Service\Data\PluginManager;
use Common\Service\Helper\ComplaintsHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\OppositionHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Application\UndoGrant;
use Dvsa\Olcs\Transfer\Command\ChangeOfEntity\CreateChangeOfEntity as CreateChangeOfEntityCmd;
use Dvsa\Olcs\Transfer\Command\ChangeOfEntity\DeleteChangeOfEntity as DeleteChangeOfEntityCmd;
use Dvsa\Olcs\Transfer\Command\ChangeOfEntity\UpdateChangeOfEntity as UpdateChangeOfEntityCmd;
use Dvsa\Olcs\Transfer\Query\Application\Application;
use Dvsa\Olcs\Transfer\Query\Cases\ByApplication as CasesByApplication;
use Dvsa\Olcs\Transfer\Query\ChangeOfEntity\ChangeOfEntity as ChangeOfEntityQry;
use Laminas\Http\Response;
use Laminas\View\HelperPluginManager;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractController;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Olcs\Controller\Traits;

/**
 * Application Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationController extends AbstractController implements ApplicationControllerInterface
{
    use Traits\LicenceControllerTrait;
    use Traits\ApplicationControllerTrait;
    use CheckForCrudAction;

    protected FlashMessengerHelperService $flashMessengerHelper;

    protected $navigation;

    public function __construct(
        ScriptFactory $scriptFactory,
        FormHelperService $formHelper,
        TableFactory $tableFactory,
        HelperPluginManager $viewHelperManager,
        protected PluginManager $dataServiceManager,
        protected OppositionHelperService $oppositionHelper,
        protected ComplaintsHelperService $complaintsHelper,
        FlashMessengerHelperService $flashMessengerHelper,
        $navigation
    ) {
        parent::__construct(
            $scriptFactory,
            $formHelper,
            $tableFactory,
            $viewHelperManager
        );
        $this->flashMessengerHelper = $flashMessengerHelper;
        $this->navigation = $navigation;
    }

    /**
     * Placeholder stub
     *
     * @return ViewModel
     */
    public function caseAction()
    {
        $httpResponse = $this->checkForCrudAction('case', [], 'case');
        if ($httpResponse instanceof Response) {
            return $httpResponse;
        }

        $applicationId = $this->params()->fromRoute('application', null);

        $canHaveCases = $this->dataServiceManager
            ->get(\Common\Service\Data\Application::class)->canHaveCases($applicationId);

        if (!$canHaveCases) {
            $this->flashMessengerHelper
                ->addErrorMessage('The application has no cases');

            return $this->redirect()->toRouteAjax('lva-application', ['application' => $applicationId]);
        }

        $params = [
            'application' => $applicationId,
            'page'    => $this->params()->fromRoute('page', 1),
            'sort'    => $this->params()->fromRoute('sort', 'id'),
            'order'   => $this->params()->fromRoute('order', 'desc'),
            'limit'   => $this->params()->fromRoute('limit', 10),
        ];

        $params = array_merge(
            $params,
            $this->getRequest()->getQuery()->toArray(),
            ['query' => $this->getRequest()->getQuery()]
        );

        $dtoData = CasesByApplication::create($params);

        $response = $this->handleQuery($dtoData);

        $results = [];
        if ($response->isClientError() || $response->isServerError()) {
            $this->flashMessengerHelper->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            $results = $response->getResult();
        }

        $view = new ViewModel(['table' => $this->getTable('cases', $results, $params)]);
        $view->setTemplate('pages/table');

        $this->loadScripts(['table-actions']);

        return $this->render($view);
    }

    /**
     * Set method for request
     *
     * @param \Laminas\Http\Request $request Laminas
     *
     * @return void
     */
    public function setRequest(\Laminas\Http\Request $request)
    {
        $this->request = $request;
        return $this;
    }

    /**
     * Opposition page
     *
     * @return ViewModel
     */
    public function oppositionAction()
    {
        $applicationId = (int) $this->params()->fromRoute('application', null);

        $responseOppositions = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\Opposition\OppositionList::create(
                [
                    'application' => $applicationId,
                    'sort' => 'raisedDate',
                    'order' => 'ASC',
                    'page' => 1,
                    'limit' => 100,
                ]
            )
        );
        if (!$responseOppositions->isOk()) {
            throw new \RuntimeException('Cannot get Opposition list');
        }
        $oppositionResults = $responseOppositions->getResult()['results'];

        $oppositionHelperService = $this->oppositionHelper;
        $oppositions = $oppositionHelperService->sortOpenClosed($oppositionResults);

        $responseComplaints = $this->handleQuery(
            \Dvsa\Olcs\Transfer\Query\EnvironmentalComplaint\EnvironmentalComplaintList::create(
                [
                    'application' => $applicationId,
                    'sort' => 'complaintDate',
                    'order' => 'ASC',
                    'page' => 1,
                    'limit' => 100,
                ]
            )
        );
        if (!$responseComplaints->isOk()) {
            throw new \RuntimeException('Cannot get Complaints list');
        }
        $casesResults = $responseComplaints->getResult()['results'];

        $complaintsHelperService = $this->complaintsHelper;
        $complaints = $complaintsHelperService->sortCasesOpenClosed($casesResults);

        $view = new ViewModel(
            [
                'tables' => [
                    $this->getTable('opposition-readonly', $oppositions),
                    $this->getTable('environmental-complaints-readonly', $complaints)
                ]
            ]
        );
        $view->setTemplate('pages/multi-tables');

        return $this->renderView($view);
    }

    /**
     * undo grant action
     *
     * @return \Laminas\Http\Response|ViewModel
     */
    public function undoGrantAction()
    {
        $request = $this->getRequest();
        $id = $this->params('application');

        if ($request->isPost()) {
            if (!$this->isButtonPressed('cancel')) {
                $response = $this->handleCommand(UndoGrant::create(['id' => $id]));

                if ($response->isOk()) {
                    $this->flashMessengerHelper
                        ->addSuccessMessage('The application grant has been undone successfully');
                } else {
                    $this->flashMessengerHelper
                        ->addErrorMessage('unknown-error');
                }
            }

            return $this->redirect()->toRouteAjax('lva-application', ['application' => $id]);
        }

        $formHelper = $this->formHelper;

        $form = $formHelper->createFormWithRequest('GenericConfirmation', $request);

        $form->get('messages')->get('message')->setValue('confirm-undo-grant-application');

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('pages/form');

        return $this->renderView($view, 'Undo grant application');
    }

    /**
     * Get the Licence Id for Application
     *
     * @param int $applicationId applicationId
     *
     * @return mixed
     */
    protected function getLicenceIdForApplication($applicationId = null)
    {
        if (is_null($applicationId)) {
            $applicationId = $this->params()->fromRoute('application');
        }

        $response = $this->handleQuery(Application::create(['id' => $applicationId]));
        $result = $response->getResult();

        return $result['licence']['id'];
    }

    /**
     * Action to handle an application change of entity request.
     *
     * @return string|\Laminas\Http\Response|ViewModel
     */
    public function changeOfEntityAction()
    {
        $request = $this->getRequest();
        $applicationId = $this->params()->fromRoute('application', null);
        $changeOfEntity = $this->params()->fromRoute('changeId', null);

        if ($this->isButtonPressed('remove')) {
            $dto = DeleteChangeOfEntityCmd::create(['id' => $changeOfEntity]);
            $response = $this->handleCommand($dto);
            if ($response->isOk()) {
                $this->flashMessenger()->addSuccessMessage('application.change-of-entity.delete.success');
            }
            return $this->redirectToRouteAjax(
                'lva-application/overview',
                [
                    'application' => $applicationId
                ]
            );
        }

        $form = $this->formHelper
            ->createFormWithRequest('ApplicationChangeOfEntity', $request);

        if (!is_null($changeOfEntity)) {
            $dto = ChangeOfEntityQry::create(['id' => $changeOfEntity]);
            $response = $this->handleQuery($dto);
            $changeOfEntityData = $response->getResult();
            $form->setData(
                [
                    'change-details' => $changeOfEntityData
                ]
            );
        } else {
            $form->get('form-actions')->remove('remove');
        }

        if ($request->isPost()) {
            $form->setData((array)$request->getPost());

            if ($form->isValid()) {
                $details = $form->getData()['change-details'];
                if ($changeOfEntity) {
                    $dto = UpdateChangeOfEntityCmd::create(
                        [
                            'id' => $changeOfEntity,
                            'oldOrganisationName' => $details['oldOrganisationName'],
                            'oldLicenceNo' => $details['oldLicenceNo'],
                        ]
                    );
                } else {
                    $dto = CreateChangeOfEntityCmd::create(
                        [
                            'applicationId' => $applicationId,
                            'oldOrganisationName' => $details['oldOrganisationName'],
                            'oldLicenceNo' => $details['oldLicenceNo'],
                        ]
                    );
                }

                $response = $this->handleCommand($dto);

                if ($response->isOk()) {
                    $this->flashMessenger()->addSuccessMessage('application.change-of-entity.create.success');
                }

                return $this->redirectToRouteAjax(
                    'lva-application/overview',
                    [
                        'application' => $applicationId
                    ]
                );
            }
        }

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('pages/form');

        return $this->renderView($view, 'Change Entity');
    }
}
