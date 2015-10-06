<?php

/**
 * Payment Processing Controller
 */
namespace Admin\Controller;

use Common\Category;
use Common\RefData;
use Dvsa\Olcs\Transfer\Query\Document\DocumentList;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Zend\View\Model\ViewModel;
use Common\Controller\AbstractActionController;
use Dvsa\Olcs\Transfer\Query\Organisation\CpidOrganisation;
use Dvsa\Olcs\Transfer\Command\Organisation\CpidOrganisationExport;
use Dvsa\Olcs\Transfer\Query\Organisation\Organisation;

/**
 * Payment Processing Controller
 */
class PaymentProcessingController extends AbstractActionController implements LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-payment-processing';

    /**
     * Export and list the organsations by CPID.
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function cpidClassificationAction()
    {
        $this->loadScripts(['table-actions']);

        if ($this->getRequest()->isPost()) {
            if ($this->params()->fromPost('action') === 'Export') {
                $command = CpidOrganisationExport::create(
                    [
                        'cpid' => $this->params()->fromRoute('status')
                    ]
                );

                $response = $this->handleCommand($command);
                if ($response->isOk()) {
                    $this->getFlashMessenger()->addSuccessMessage('Mass Export Queued.');

                    return $this->redirectToRouteAjax(
                        'admin-dashboard/admin-payment-processing/cpid-class'
                    );
                }
            }
        }

        $status = (empty($this->params()->fromQuery('status')) ? null : $this->params()->fromQuery('status'));

        $data = [
            'action' => $this->url()->fromRoute(
                'admin-dashboard/admin-payment-processing/cpid-class',
                [
                    'status' => $status
                ]
            ),
            'page' => $this->params()->fromQuery('page', 1),
            'limit' => $this->params()->fromQuery('limit', 10)
        ];

        $query = CpidOrganisation::create(
            [
                'cpid' => $status,
                'page' => $data['page'],
                'limit' => $data['limit'],
            ]
        );

        $response = $this->handleQuery($query);
        $table = $this->getTable(
            'admin-cpid-classification',
            $response->getResult(),
            $data
        );

        $cpidFilterForm = $this->getCpidFilterForm($status);

        $view = new ViewModel(
            [
                'table' => $table,
                'filterForm' => $cpidFilterForm,
            ]
        );

        $view->setTemplate('pages/table');
        return $this->renderLayout($view);
    }

    /**
     * @return ViewModel
     */
    public function cpidExportsAction()
    {
        $data = [
            'page' => $this->params()->fromQuery('page', 1),
            'limit' => $this->params()->fromQuery('limit', 10)
        ];

        $query = DocumentList::create(
            [
                'sort' => 'issuedDate',
                'order' => 'desc',
                'category' => Category::CATEGORY_LICENSING,
                'documentSubCategory' => Category::DOC_SUB_CATEGORY_CPID,
                'page' => $data['page'],
                'limit' => $data['limit'],
            ]
        );

        $response = $this->handleQuery($query);
        $table = $this->getTable(
            'admin-cpid-exports',
            $response->getResult(),
            $data
        );

        $view = new ViewModel(['table' => $table]);
        $view->setTemplate('pages/table');

        return $this->renderLayout($view);
    }

    /**
     * @inheritdoc
     */
    protected function renderLayout($view, $pageTitle = null, $pageSubTitle = null)
    {
        $this->getViewHelperManager()->get('placeholder')->getContainer('tableFilters')
            ->set($view->getVariable('filterForm'));

        return parent::renderView($view, 'Payment processing', $pageSubTitle);
    }

    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-payment-processing',
                'navigationTitle' => 'Payment Processing'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * Redirect action
     *
     * @return \Zend\Http\Response
     */
    public function redirectAction()
    {
        return $this->redirectToRouteAjax(
            'admin-dashboard/admin-payment-processing/misc-fees',
            ['action'=>'index'],
            ['code' => '303'],
            true
        );
    }

    /**
     * Get the CPID filter form.
     *
     * @param $status
     *
     * @return \Common\Controller\type
     */
    private function getCpidFilterForm($status)
    {
        $cpidFilterForm = $this->getForm('cpid-filter');
        $cpidFilterForm->remove('security');
        $cpidFilterForm->setData(['status' => $status]);
        $cpidFilterForm->get('status')->addValueOption([RefData::OPERATOR_CPID_ALL => 'All']);

        return $cpidFilterForm;
    }
}
