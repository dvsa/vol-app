<?php

namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Common\Service\Entity\LicenceEntityService;
use Common\Controller\Lva\Traits\CrudActionTrait;
use Dvsa\Olcs\Transfer\Query\ContinuationDetail\GetList as GetListQry;
use Dvsa\Olcs\Transfer\Command\Continuation\Create as CreateCmd;
use Dvsa\Olcs\Transfer\Command\ContinuationDetail\PrepareContinuations as PrepareCmd;

/**
 * Continuation Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ContinuationController extends AbstractController
{
    use CrudActionTrait;

    const CONTINUATION_TYPE_IRFO = 'irfo';

    protected $defaultFilters = [
        'licenceStatus' => [
            LicenceEntityService::LICENCE_STATUS_VALID,
            LicenceEntityService::LICENCE_STATUS_SUSPENDED,
            LicenceEntityService::LICENCE_STATUS_CURTAILED,
            LicenceEntityService::LICENCE_STATUS_REVOKED,
            LicenceEntityService::LICENCE_STATUS_SURRENDERED,
            LicenceEntityService::LICENCE_STATUS_TERMINATED
        ]
    ];

    protected $detailRoute = 'admin-dashboard/admin-continuation/detail';

    /**
     * Action: index
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function indexAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();
        $form = $this->getContinuationForm();

        if ($request->isPost()) {

            $data = (array)$request->getPost();

            $form->setData($data);
        }

        if ($request->isPost() && $form->isValid()) {
            $data = $form->getData();

            list($year, $month) = explode('-', $data['details']['date']);

            if ($data['details']['type'] === self::CONTINUATION_TYPE_IRFO) {
                // redirect to irfo psv auth continuation page
                return $this->redirect()->toRoute(
                    'admin-dashboard/admin-continuation/irfo-psv-auth',
                    [
                        'month' => (int)$month,
                        'year' => (int)$year,
                    ]
                );
            }

            $criteria = [
                'month' => (int)$month,
                'year' => (int)$year,
                'trafficArea' => $data['details']['trafficArea']
            ];

            $response = $this->handleCommand(
                CreateCmd::create($criteria)
            );

            $fm = $this->getServiceLocator()->get('Helper\FlashMessenger');
            if ($response->isServerError() || $response->isClientError()) {
                $fm->addCurrentErrorMessage('unknown-error');
            }
            if ($response->isOk()) {
                $continuationId = $response->getResult()['id']['continuation'];

                // no licences found
                if (!$continuationId) {
                    $fm->addCurrentInfoMessage('admin-continuations-no-licences-found');
                } else {
                    // continuation created or already exists
                    return $this->redirect()->toRoute($this->detailRoute, ['id' => $continuationId]);
                }
            }
        }

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('pages/form');
        $this->setNavigationId('admin-dashboard/continuations');
        $this->getServiceLocator()->get('Script')->loadFile('continuations');

        return $this->renderView($view, 'admin-generate-continuations-title');
    }

    /**
     * Action: detail
     *
     * @return ViewModel | \Zend\Http\Response
     */
    public function detailAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {

            $data = (array)$request->getPost();

            $crudAction = $this->getCrudAction([$data]);

            if ($crudAction !== null) {
                return $this->handleCrudAction($crudAction);
            }
        }

        $id = $this->params('id');

        $translationHelper = $this->getServiceLocator()->get('Helper\Translation');
        /** @var \Common\Service\Table\TableBuilder $tableHelper */
        $tableHelper = $this->getServiceLocator()->get('Table');

        $filterForm = $this->getDetailFilterForm();
        if ($filterForm->isValid()) {
            $filters = $filterForm->getData()['filters'];
        } else {
            $filters = [];
        }
        list($tableData, $data) = $this->getContinuationData($id, $filters);

        $period = date('M Y', strtotime($data['year'] . '-' . $data['month'] . '-01'));

        $title = $translationHelper->translateReplace(
            'admin-continuations-list-title',
            [$period, $data['name']]
        );

        $table = $tableHelper->prepareTable('admin-continuations', $tableData);
        $table->setVariable('title', $tableData['count'] . ' licence(s)');

        $this->getServiceLocator()->get('Script')->loadFiles(['forms/filter', 'table-actions']);

        $this->getServiceLocator()->get('viewHelperManager')->get('placeholder')
            ->getContainer('tableFilters')->set($filterForm);

        $this->setNavigationId('admin-dashboard/continuations-details');

        $view = new ViewModel(['table' => $table, 'filterForm' => $filterForm]);
        $view->setTemplate('pages/table');

        return $this->renderView($view, 'admin-generate-continuation-details-title', $title);
    }

    /**
     * Action: generate
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function generateAction()
    {
        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();

        if ($request->isPost()) {

            $data = (array)$request->getPost();

            if (isset($data['form-actions']['cancel'])) {
                return $this->redirect()->toRoute(null, ['action' => null, 'child_id' => null], [], true);
            }

            $ids = explode(',', $this->params('child_id'));

            $response = $this->handleCommand(
                PrepareCmd::create(
                    [
                        'ids' => $ids
                    ]
                )
            );
            $flashMessenger = $this->getServiceLocator()->get('Helper\FlashMessenger');
            if ($response->isOk()) {
                $flashMessenger->addSuccessMessage('The selected licence(s) have been queued');
            }
            if ($response->isServerError() || $response->isClientError()) {
                $flashMessenger->addErrorMessage('The selected licence(s) could not be queued, please try again');
            }

            return $this->redirect()->toRouteAjax(null, ['action' => null, 'child_id' => null], [], true);
        }

        $form = $this->getServiceLocator()->get('Helper\Form')
            ->createFormWithRequest('Confirmation', $request);

        $params = [
            'form' => $form,
            'sectionText' => 'continuaton-generate-confirm'
        ];

        $view = new ViewModel($params);
        $view->setTemplate('pages/form');
        $this->setNavigationId('admin-dashboard/continuations');

        return $this->renderView($view, 'Generate continuations');
    }

    /**
     * Get Detail Filter Form
     *
     * @return \Zend\Form\FormInterface
     */
    protected function getDetailFilterForm()
    {
        $query = (array)$this->params()->fromQuery('filters');

        $filters = array_merge($this->defaultFilters, $query);

        return $this->getServiceLocator()->get('Helper\Form')
            ->createForm('ContinuationDetailFilter', false)
            ->setData(['filters' => $filters]);
    }

    /**
     * Get Continuation Data
     *
     * @param string $id      Continuation Id
     * @param array  $filters Filters
     *
     * @return array
     */
    protected function getContinuationData($id, $filters)
    {
        $filters = array_merge($filters, ['continuationId' => $id]);
        if (!$filters['method']) {
            $filters['method'] = 'all';
        }

        $result = [];
        $header = [];

        $response = $this->handleQuery(GetListQry::create($filters));
        if ($response->isOk()) {
            $result = $response->getResult();
            $header = $response->getResult()['header'];
        }
        if ($response->isServerError() || $response->isClientError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }
        return [
            $result,
            $header
        ];
    }

    /**
     * Get Continuation Form
     *
     * @return \Zend\Form\FormInterface
     */
    protected function getContinuationForm()
    {
        return $this->getServiceLocator()->get('Helper\Form')
            ->createForm('GenerateContinuation');
    }
}
