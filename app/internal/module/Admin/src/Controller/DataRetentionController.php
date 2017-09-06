<?php

namespace Admin\Controller;

use Dvsa\Olcs\Transfer\Query\DataRetention\GetRule;
use Dvsa\Olcs\Transfer\Query\DataRetention\Records as RecordsListDto;
use Dvsa\Olcs\Transfer\Query\DataRetention\RuleList as ListDto;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Common\Controller\Traits\GenericRenderView;
use Olcs\Controller\AbstractInternalController;
use Zend\View\Model\ViewModel;

/**
 * Data retention controller
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
    protected $tableName = 'admin-data-retention-rules';
    protected $recordsTableName = 'admin-data-retention-records';

    protected $defaultTableSortField = 'id';
    protected $defaultTableOrderField = 'DESC';

    protected $listDto = ListDto::class;
    protected $recordsListDto = RecordsListDto::class;

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
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'Data retention rules');

        return parent::indexAction();
    }

    /**
     * Records action
     *
     * @return \Zend\Http\Response|ViewModel
     */
    public function recordsAction()
    {
        $ruleId = $this->params('dataRetentionRuleId');
        $query = GetRule::create(['id' => $ruleId]);

        $response = $this->handleQuery($query);
        $dataRetentionRule = $response->getResult();

        $this->placeholder()->setPlaceholder(
            'pageTitle',
            ucwords($dataRetentionRule['description'])
        );

        $this->tableName = $this->recordsTableName;
        $this->listDto = $this->recordsListDto;
        $this->listVars = ['dataRetentionRuleId'];
        $this->defaultTableLimit = 25;

        return parent::indexAction();
    }
}
