<?php

/**
 * Hearing & Appeal Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller\Cases\Hearing;

use Dvsa\Olcs\Transfer\Command\Cases\Hearing\CreateAppeal as CreateDto;
use Dvsa\Olcs\Transfer\Command\Cases\Hearing\UpdateAppeal as UpdateDto;
use Dvsa\Olcs\Transfer\Command\Cases\Hearing\DeleteAppeal as DeleteDto;
use Dvsa\Olcs\Transfer\Query\Cases\Hearing\Appeal as AppealDto;
use Dvsa\Olcs\Transfer\Query\Cases\Hearing\AppealList as ListDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Form\Model\Form\Appeal as FormClass;
use Olcs\Data\Mapper\Appeal as Mapper;
use Zend\View\Model\ViewModel;

/**
 * Hearing Appeal Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class AppealController extends AbstractInternalController implements CaseControllerInterface, LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_hearings_appeals_stays';

    protected $routeIdentifier = 'appeal';

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/table-comments';
    protected $defaultTableSortField = 'n/a';
    protected $tableName = 'appeal';
    protected $listDto = ListDto::class;
    protected $listVars = ['case'];

    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/cases/partials/left');

        return $view;
    }

    /**
     * Variables for controlling details view rendering
     * details view and itemDto are required.
     */
    protected $detailsViewTemplate = 'sections/cases/pages/appeals-stays';
    protected $detailsViewPlaceholderName = '  ';
    protected $itemDto = AppealDto::class;
    // 'id' => 'conviction', to => from
    protected $itemParams = [
        'case',
        'id' => 'appeal',
    ];

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = FormClass::class;
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = Mapper::class;
    protected $addContentTitle = 'Add appeal';
    protected $editContentTitle = 'Edit appeal';

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $createCommand = CreateDto::class;

    /**
     * Form data for the add form.
     *
     * Format is name => value
     * name => "route" means get value from route,
     * see conviction controller
     *
     * @var array
     */
    protected $defaultData = [
        'case' => 'route'
    ];

    /**
     * Variables for controlling the delete action.
     * Command is required, as are itemParams from above
     */
    protected $deleteCommand = DeleteDto::class;

    protected $inlineScripts = array('forms/hearings-appeal');

    /**
     * Allows override of default behaviour for redirects. See Case Overview Controller
     *
     * @var array
     */
    protected $redirectConfig = [
        'add' => [
            'action' => 'details',
            'route' => 'case_hearing_appeal',
            'reUseParams' => true,
        ],
        'edit' => [
            'action' => 'details',
            'route' => 'case_hearing_appeal',
            'reUseParams' => true,
        ],
    ];
}
