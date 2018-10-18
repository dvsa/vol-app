<?php

namespace Admin\Controller;

use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Zend\View\Model\ViewModel;
use Common\Category;
use Dvsa\Olcs\Transfer\Query\Document\DocumentList;

class IrhpPermitReportingController extends AbstractInternalController implements LeftViewProvider
{
    protected $navigationId = 'admin-dashboard/admin-permits';
    protected $tableViewTemplate = 'pages/irhp-permit-reporting/index';
    protected $tableName = 'admin-exported-reports';
    protected $defaultTableSortField = 'issuedDate';
    protected $defaultTableOrderField = 'DESC';
    protected $listDto = DocumentList::class;

    /**
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel(
            [
                'navigationId' => 'admin-dashboard/admin-permits',
                'navigationTitle' => 'Permits',
                'stockId' => $this->params()->fromRoute()['stockId']
            ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    /**
     * Extra parameters
     *
     * @param array $parameters parameters
     *
     * @return array
     */
    protected function modifyListQueryParameters($parameters)
    {
        $parameters['category'] = Category::CATEGORY_PERMITS;
        $parameters['documentSubCategory'] = [Category::DOC_SUB_CATEGORY_PERMITS];
        $parameters['onlyUnlinked'] = 'Y';

        return $parameters;
    }
}
