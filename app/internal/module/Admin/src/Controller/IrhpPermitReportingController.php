<?php

namespace Admin\Controller;

use Common\Category;
use Dvsa\Olcs\Transfer\Query\Document\DocumentList;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\Interfaces\LeftViewProvider;

class IrhpPermitReportingController extends AbstractIrhpPermitAdminController implements LeftViewProvider
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
                'navigationTitle' => '',
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
    #[\Override]
    protected function modifyListQueryParameters($parameters)
    {
        $parameters['category'] = Category::CATEGORY_PERMITS;
        $parameters['documentSubCategory'] = [Category::DOC_SUB_CATEGORY_PERMITS];
        $parameters['onlyUnlinked'] = 'Y';

        return $parameters;
    }
}
