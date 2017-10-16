<?php

namespace Admin\Controller\DataRetention;

use Dvsa\Olcs\Transfer\Query\DataRetention\RuleAdmin as ListDto;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\AbstractInternalController;
use Zend\View\Model\ViewModel;

/**
 * Rule admin controller
 */
class RuleAdminController extends AbstractInternalController implements LeftViewProvider
{

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-data-retention';

    protected $defaultTableSortField = 'id';
    protected $defaultTableOrderField = 'DESC';

    protected $listDto = ListDto::class;

    protected $tableName = 'admin-data-retention-rules-admin';
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/table';

    protected $itemParams = ['ids' => 'id'];

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
        $this->placeholder()->setPlaceholder('pageTitle', 'Rules admin');
        return parent::indexAction();
    }

}
