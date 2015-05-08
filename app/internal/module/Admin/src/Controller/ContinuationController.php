<?php

/**
 * Continuation Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Admin\Controller;

use Zend\View\Model\ViewModel;
use Common\Service\Entity\ContinuationEntityService;
use Common\BusinessService\Response;
use Common\Service\Entity\LicenceEntityService;
use Common\Controller\Lva\Traits\CrudActionTrait;

/**
 * Continuation Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ContinuationController extends AbstractController
{
    use CrudActionTrait;

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

    public function indexAction()
    {
        $request = $this->getRequest();
        $form = $this->getContinuationForm();

        if ($request->isPost()) {

            $data = (array)$request->getPost();

            $form->setData($data);
        }

        if ($request->isPost() && $form->isValid()) {
            $data = $form->getData();

            // AC Says to redirect to placeholder page until irfo is developed
            if ($data['details']['type'] === ContinuationEntityService::TYPE_IRFO) {
                return $this->redirect()->toRoute(null, ['action' => 'irfo']);
            }

            list($year, $month) = explode('-', $data['details']['date']);

            $criteria = [
                'month' => (int)$month,
                'year' => (int)$year,
                'trafficArea' => $data['details']['trafficArea']
            ];

            $continuation = $this->getServiceLocator()->get('Entity\Continuation')->find($criteria);

            if ($continuation !== null) {
                return $this->redirect()->toRoute($this->detailRoute, ['id' => $continuation['id']]);
            }

            // Create continuation
            $response = $this->getServiceLocator()->get('BusinessServiceManager')
                ->get('Admin\Continuation')
                ->process(['data' => $criteria]);

            // We treat success and no_op differently in this case
            if ($response->getType() === Response::TYPE_SUCCESS) {

                $id = $response->getData()['id'];
                return $this->redirect()->toRoute($this->detailRoute, ['id' => $id]);
            }

            $fm = $this->getServiceLocator()->get('Helper\FlashMessenger');

            if ($response->getType() === Response::TYPE_NO_OP) {
                $fm->addCurrentInfoMessage('admin-continuations-no-licences-found');
            } else {
                $fm->addCurrentErrorMessage($response->getMessage());
            }
        }

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('partials/form');
        $this->setNavigationId('admin-dashboard/continuations');
        $this->getServiceLocator()->get('Script')->loadFile('continuations');

        return $this->renderView($view, 'admin-generate-continuations-title');
    }

    public function detailAction()
    {
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
        $continuationEntity = $this->getServiceLocator()->get('Entity\Continuation');
        $tableHelper = $this->getServiceLocator()->get('Table');

        $data = $continuationEntity->getHeaderData($id);

        $period = date('M Y', strtotime($data['year'] . '-' . $data['month'] . '-01'));

        $title = $translationHelper->translateReplace(
            'admin-continuations-list-title',
            [$period, $data['trafficArea']['name']]
        );

        $filterForm = $this->getDetailFilterForm();

        if ($filterForm->isValid()) {
            $filters = $filterForm->getData()['filters'];
        } else {
            $filters = [];
        }

        $tableData = $this->getContinuationDetailTableData($id, $filters);

        $table = $tableHelper->prepareTable('admin-continuations', $tableData);
        $table->setVariable('title', $tableData['Count'] . ' licence(s)');

        $this->getServiceLocator()->get('Script')->loadFiles(['forms/filter', 'table-actions']);

        $view = new ViewModel(['table' => $table, 'filterForm' => $filterForm]);
        $view->setTemplate('partials/table');
        return $this->renderView($view, 'admin-generate-continuation-details-title', $title);
    }

    public function irfoAction()
    {
        $view = new ViewModel();
        $view->setTemplate('placeholder');
        $this->setNavigationId('admin-dashboard/continuations');
        return $this->renderView($view, 'IRFO Continuations');
    }

    public function generateAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {

            $data = (array)$request->getPost();

            if (isset($data['form-actions']['cancel'])) {
                return $this->redirect()->toRoute(null, ['action' => null, 'child_id' => null], [], true);
            }

            $ids = explode(',', $this->params('child_id'));

            $response = $this->getServiceLocator()->get('BusinessServiceManager')
                ->get('Admin\ContinuationDetailMessage')
                ->process(['ids' => $ids]);

            $flashMessenger = $this->getServiceLocator()->get('Helper\FlashMessenger');

            if ($response->isOk()) {
                $flashMessenger->addSuccessMessage('The selected licence(s) have been queued');
            } else {
                $message = $response->getMessage();
                if ($message === null) {
                    $message = 'The selected licence(s) could not be queued, please try again';
                }
                $flashMessenger->addErrorMessage($message);
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
        $view->setTemplate('partials/form');
        $this->setNavigationId('admin-dashboard/continuations');
        return $this->renderView($view, 'Generate checklists');
    }

    public function printPageAction()
    {
        $view = new ViewModel();
        $view->setTemplate('placeholder');
        $this->setNavigationId('admin-dashboard/continuations');
        return $this->renderView($view, 'Print page');
    }

    protected function getDetailFilterForm()
    {
        $query = (array)$this->params()->fromQuery('filters');

        $filters = array_merge($this->defaultFilters, $query);

        return $this->getServiceLocator()->get('Helper\Form')
            ->createForm('ContinuationDetailFilter', false)
            ->setData(['filters' => $filters]);
    }

    protected function getContinuationDetailTableData($id, $filters)
    {
        return $this->getServiceLocator()->get('Entity\ContinuationDetail')
            ->getListData($id, $filters);
    }

    protected function getContinuationForm()
    {
        return $this->getServiceLocator()->get('Helper\Form')
            ->createForm('GenerateContinuation');
    }
}
