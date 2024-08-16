<?php

namespace Admin\Controller;

use Admin\Controller\Traits\ReportLeftViewTrait;
use Common\Category;
use Common\Controller\Traits\GenericMethods;
use Common\Controller\Traits\GenericRenderView;
use Common\Controller\Traits\ViewHelperManagerAware;
use Common\RefData;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\Organisation\CpidOrganisationExport;
use Dvsa\Olcs\Transfer\Query\Document\DocumentList;
use Dvsa\Olcs\Transfer\Query\Organisation\CpidOrganisation;
use Laminas\Mvc\Controller\AbstractActionController as LaminasAbstractActionController;
use Laminas\View\Helper\Placeholder;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Interfaces\LeftViewProvider;

class ReportController extends LaminasAbstractActionController implements LeftViewProvider
{
    use GenericMethods;
    use GenericRenderView;
    use ViewHelperManagerAware;
    use ReportLeftViewTrait;

    protected ScriptFactory $scriptFactory;
    protected TableFactory $tableFactory;
    protected FormHelperService $formHelper;

    public function __construct(
        ScriptFactory $scriptFactory,
        TableFactory $tableFactory,
        FormHelperService $formHelper,
        protected Placeholder $placeholder
    ) {
        $this->scriptFactory = $scriptFactory;
        $this->tableFactory = $tableFactory;
        $this->formHelper = $formHelper;
    }

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
        $this->placeholder->getContainer('tableFilters')
            ->set($view->getVariable('filterForm'));

        return $this->renderView($view, $pageTitle, $pageSubTitle);
    }

    /**
     * index action
     *
     * @return \Laminas\Http\Response
     */
    public function indexAction()
    {
        return $this->redirectToRoute('admin-dashboard/admin-report/ch-alerts', [], null, true);
    }

    /**
     * Export and list the organsations by CPID.
     *
     * @return \Laminas\Http\Response|ViewModel
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

                $flashMessenger = $this->flashMessenger();
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
            'limit' => $this->params()->fromQuery('limit', 10),
            'query' => $this->getRequest()->getQuery()->toArray(),
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
