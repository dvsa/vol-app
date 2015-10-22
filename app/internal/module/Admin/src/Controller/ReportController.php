<?php
/**
 * Report Controller
 */

namespace Admin\Controller;

use Common\Category;
use Common\Controller\AbstractActionController;
use Common\RefData;
use Dvsa\Olcs\Transfer\Command\Organisation\CpidOrganisationExport;
use Dvsa\Olcs\Transfer\Query\Document\DocumentList;
use Dvsa\Olcs\Transfer\Query\Organisation\CpidOrganisation;
use Dvsa\Olcs\Transfer\Query\Organisation\Organisation;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Zend\View\Model\ViewModel;

/**
 * Report Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

class ReportController extends AbstractActionController implements LeftViewProvider
{
    /**
     * @inheritdoc
     */
    protected function renderLayout($view, $pageTitle = 'Reports', $pageSubTitle = null)
    {
        $this->getViewHelperManager()->get('placeholder')->getContainer('tableFilters')
            ->set($view->getVariable('filterForm'));

        return parent::renderView($view, $pageTitle, $pageSubTitle);
    }

    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-report',
                'navigationTitle' => 'Reports'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    public function indexAction()
    {
        return $this->redirectToRoute('admin-dashboard/admin-report/ch-alerts', [], null, true);
    }

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
                        'admin-dashboard/admin-report/cpid-class'
                    );
                }
            }
        }

        $status = (empty($this->params()->fromQuery('status')) ? null : $this->params()->fromQuery('status'));

        $data = [
            'action' => $this->url()->fromRoute(
                'admin-dashboard/admin-report/cpid-class',
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
        return $this->renderLayout($view, 'CPID classification');
    }

    /**
     * @return ViewModel
     */
    public function exportedReportsAction()
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
                'documentSubCategory' => [
                    Category::DOC_SUB_CATEGORY_CPID,
                    Category::DOC_SUB_CATEGORY_FINANCIAL_REPORTS,
                ],
                'page' => $data['page'],
                'limit' => $data['limit'],
            ]
        );

        $response = $this->handleQuery($query);
        $table = $this->getTable(
            'admin-exported-reports',
            $response->getResult(),
            $data
        );

        $view = new ViewModel(['table' => $table]);
        $view->setTemplate('pages/table');

        return $this->renderLayout($view, 'Exported reports');
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
