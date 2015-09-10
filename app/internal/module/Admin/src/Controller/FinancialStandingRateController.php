<?php

/**
 * Financial Standing Rate Controller
 */
namespace Admin\Controller;

use Dvsa\Olcs\Transfer\Query\System\FinancialStandingRateList as ListDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\PageInnerLayoutProvider;
use Olcs\Controller\Interfaces\PageLayoutProvider;
use Zend\View\Model\ViewModel;

/**
 * Financial Standing Rate Controller
 */
class FinancialStandingRateController extends AbstractInternalController implements PageLayoutProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'admin-dashboard/admin-financial-standing';

    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
    ];

    /*
     * Variables for controlling table/list rendering
     */
    protected $tableName = 'admin-financial-standing';
    protected $defaultTableSortField = 'effectiveFrom';
    protected $listDto = ListDto::class;
    protected $itemParams = ['id'];

    public function getPageLayout()
    {
        return 'layout/admin-layout';
    }

    public function indexAction()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'Financial standing rates');

        return parent::indexAction();
    }
}
