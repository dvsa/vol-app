<?php

/**
 * System Parameters Controller
 *
 * @author Alexander Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Admin\Controller;

use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Zend\View\Model\ViewModel;

use Dvsa\Olcs\Transfer\Query\SystemParameter\SystemParameterList as ListDto;
use Dvsa\Olcs\Transfer\Query\Sectors\Sectors as SiftDto;


/**
 * Sifting Controller
 *
 * @author Tonci Vidovic <tonci.vidovic@capgemini.com>
 */
class SiftingResultsController extends AbstractInternalController implements LeftViewProvider
{

    protected $navigationId = 'admin-dashboard/admin-sifting';

    /**
     * @var array
     */
    protected $inlineScripts = [
      'indexAction' => ['table-actions'],
    ];

    // list
    protected $tableName = 'admin-sifting-results';
    protected $defaultTableSortField = 'id';
    protected $defaultTableOrderField = 'ASC';
    protected $listDto = ListDto::class;
    protected $siftDto = SiftDto::class;

    protected $tableViewTemplate = 'pages/sifting/sifting-results';


    public function getLeftView()
    {
        $view = new ViewModel(
          [
            'navigationId' => 'admin-dashboard/admin-sifting',
            'navigationTitle' => 'Sifting results'
          ]
        );
        $view->setTemplate('admin/sections/admin/partials/generic-left');

        return $view;
    }

    public function indexAction()
    {

        $this->placeholder()->setPlaceholder('pageTitle', 'Sifting results');

        return parent::indexAction();
    }


}
