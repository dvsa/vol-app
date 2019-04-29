<?php

namespace Admin\Controller;

use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Zend\View\Model\ViewModel;
use Dvsa\Olcs\Transfer\Query\Application\InterimRefunds as ListDto;

/**
 * Class InterimRefundsController
 *
 * @package Admin\Controller
 */
class InterimRefundsController extends AbstractInternalController implements LeftViewProvider
{
    protected $navigationId = 'admin-dashboard/admin-interim-refunds';


    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
    ];

    // list
    protected $tableName = 'admin-interim-refunds';
    protected $defaultTableSortField = 'id';
    protected $defaultTableOrderField = 'ASC';
    protected $listDto = ListDto::class;



    protected $tableViewTemplate = 'pages/interim-refund/index';

    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-interim-refunds',
                'navigationTitle' => 'Interim Refunds'
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    public function indexAction()
    {
        $this->placeholder()->setPlaceholder('pageTitle', 'Interim Refunds');

        return parent::indexAction();
    }
}
