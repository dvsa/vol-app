<?php
/**
 * Report Controller
 */

namespace Admin\Controller;

use Common\Category;
use \Zend\Mvc\Controller\AbstractActionController as ZendAbstractActionController;
use Common\RefData;
use Dvsa\Olcs\Transfer\Command\Organisation\CpidOrganisationExport;
use Dvsa\Olcs\Transfer\Query\Document\DocumentList;
use Dvsa\Olcs\Transfer\Query\Organisation\CpidOrganisation;
use Dvsa\Olcs\Transfer\Query\Organisation\Organisation;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Zend\View\Model\ViewModel;
use Common\Controller\Traits\GenericMethods;
use Common\Controller\Traits\GenericRenderView;
use Common\Controller\Traits\ViewHelperManagerAware;

/**
 * Report Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

class ReportController extends ZendAbstractActionController implements LeftViewProvider
{
    use GenericMethods,
        GenericRenderView,
        ViewHelperManagerAware;

    /**
     * render layout
     *
     * @param ViewModel   $view         view model
     * @param string      $pageTitle    page title
     * @param string|null $pageSubTitle page sub title
     *
     * @return ViewModel
     */
    protected function renderLayout($view, $pageTitle = 'Reports', $pageSubTitle = null)
    {
        $this->getViewHelperManager()->get('placeholder')->getContainer('tableFilters')
            ->set($view->getVariable('filterForm'));

        return $this->renderView($view, $pageTitle, $pageSubTitle);
    }

    /**
     * get left view
     *
     * @return ViewModel
     */
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

    /**
     * index action
     *
     * @return \Zend\Http\Response
     */
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

                $flashMessenger = $this->getServiceLocator()->get('Helper\FlashMessenger');
                $response = $this->handleCommand($command);
                if ($response->isOk()) {
                    $flashMessenger->addSuccessMessage('Mass Export Queued.');

                    return $this->redirectToRouteAjax(
                        'admin-dashboard/admin-report/cpid-class'
                    );
                }
                $flashMessenger->addSuccessMessage('Unknown error');
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
     * exported reports action
     *
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
                'onlyUnlinked' => 'Y',
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
     * @param string $status status
     *
     * @return \Common\Form\Form
     */
    private function getCpidFilterForm($status)
    {
        $cpidFilterForm = $this->getForm('CpidFilter');
        $cpidFilterForm->remove('security');
        $cpidFilterForm->setData(['status' => $status]);
        $cpidFilterForm->get('status')->addValueOption([RefData::OPERATOR_CPID_ALL => 'All']);

        return $cpidFilterForm;
    }
}
