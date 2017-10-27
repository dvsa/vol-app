<?php

namespace Admin\Controller;

use Dvsa\Olcs\Transfer\Query\DataRetention\RuleList as ListDto;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\AbstractInternalController;
use Zend\View\Model\ViewModel;

/**
 * Data retention review controller
 */
class DataRetentionReviewController extends AbstractInternalController implements LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-data-retention';
    protected $listDto = ListDto::class;
    protected $tableName = 'admin-data-retention-rules';

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
}
