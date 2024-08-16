<?php

namespace Olcs\Controller\Licence;

use Dvsa\Olcs\Transfer\Query\Bus\SearchViewList as ListDto;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Form\Model\Form\BusRegList as FilterForm;

class BusRegistrationController extends AbstractInternalController implements
    LicenceControllerInterface,
    LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'licence_bus';

    protected $crudConfig = [
        'add' => ['route' => 'licence/bus/registration', 'requireRows' => false],
        'edit' => ['route' => 'licence/bus/registration', 'requireRows' => true]
    ];

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/table';
    protected $defaultTableSortField = 'routeNo';
    protected $defaultTableOrderField = 'ASC';
    protected $tableName = 'busreg';
    protected $listDto = ListDto::class;
    protected $listVars = [
        'licId' => 'licence'
    ];
    protected $filterForm = FilterForm::class;

    /**
     * Any inline scripts needed in this section
     *
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['forms/filter', 'table-actions']
    ];

    /**
     * Set left view
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/bus/partials/list-left');

        return $view;
    }
}
