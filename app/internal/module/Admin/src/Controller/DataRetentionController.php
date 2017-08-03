<?php

namespace Admin\Controller;

use Dvsa\Olcs\Transfer\Query\TeamPrinter\TeamPrinterExceptionsList as TeamPrinterExceptionsListDto;
use Dvsa\Olcs\Transfer\Query\DataRetention\RuleList as ListDto;
use Common\Controller\Traits\GenericRenderView;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Zend\View\Model\ViewModel;

/**
 * Data retention controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class DataRetentionController extends AbstractInternalController implements LeftViewProvider
{
    use GenericRenderView;

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-data-retention';

    // list
    protected $tableName = 'admin-data-retention';
    protected $defaultTableSortField = 'id';
    protected $defaultTableOrderField = 'ASC';
    protected $listDto = ListDto::class;
    protected $tableViewTemplate = 'pages/table';

    /**
     * Get left view
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-data-retention',
                'navigationTitle' => 'Data retention'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * Index action
     *
     * @return \Olcs\View\Model\ViewModel
     */
    public function indexAction()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'Data Retention');

        return parent::indexAction();
    }

    /**
     * Set navigation id
     *
     * @param int $id Id
     *
     * @return void
     */
    protected function setNavigationId($id)
    {
        $this->getServiceLocator()->get('viewHelperManager')->get('placeholder')
            ->getContainer('navigationId')->set($id);
    }

    /**
     * Render view
     *
     * @param \Zend\Form\Form $form      Form
     * @param int             $noOfTasks No of tasks
     *
     * @return \Zend\View\Model\ViewModel
     */
    protected function renderView($form, $noOfTasks)
    {
        $view = new ViewModel();
        $view->setVariable('form', $form);
        $view->setVariable(
            'label',
            $this->getServiceLocator()->get('Helper\Translation')
                ->translateReplace('internal.admin.remove-team-label', [$noOfTasks])
        );
        $view->setTemplate('pages/confirm');
        $this->placeholder()->setPlaceholder('pageTitle', $this->deleteModalTitle);
        return $this->viewBuilder()->buildView($view);
    }

    /**
     * Get table data
     *
     * @return array
     */
    protected function getTableData()
    {
        if (empty($this->params()->fromRoute('team'))) {
            return [];
        }

        $data = [
            'team' => $this->params()->fromRoute('team'),
        ];
        $response = $this->handleQuery(TeamPrinterExceptionsListDto::create($data));

        if ($response->isServerError() || $response->isClientError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            return $response->getResult();
        }

        return [];
    }
}
