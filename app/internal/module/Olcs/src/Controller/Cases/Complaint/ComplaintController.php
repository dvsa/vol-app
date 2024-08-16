<?php

/**
 * Case Complaint Controller
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace Olcs\Controller\Cases\Complaint;

use Dvsa\Olcs\Transfer\Command\Complaint\CreateComplaint as CreateDto;
use Dvsa\Olcs\Transfer\Command\Complaint\DeleteComplaint as DeleteDto;
use Dvsa\Olcs\Transfer\Command\Complaint\UpdateComplaint as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Complaint\Complaint as ItemDto;
use Dvsa\Olcs\Transfer\Query\Complaint\ComplaintList as ListDto;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\Complaint as Mapper;
use Olcs\Form\Model\Form\Complaint as Form;

/**
 * Case Complaint Controller
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class ComplaintController extends AbstractInternalController implements
    CaseControllerInterface,
    LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_details_complaints';

    protected $routeIdentifier = 'complaint';

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/table-comments';
    protected $defaultTableSortField = 'id';
    protected $tableName = 'complaint';
    protected $listDto = ListDto::class;
    protected $listVars = ['case'];

    /**
     * get method for Left View
     *
     * @return ViewModel
     */
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
    protected $itemDto = ItemDto::class;
    // 'id' => 'complaint', to => from
    protected $itemParams = ['id' => 'complaint'];

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = Form::class;
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = Mapper::class;
    protected $addContentTitle = 'Add complaint';
    protected $editContentTitle = 'Edit complaint';

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
    protected $deleteModalTitle = 'internal.delete-action-trait.title';
    /**
     * Variables for controlling the delete action.
     * Format is: required => supplied
     */
    protected $deleteParams = ['id' => 'complaint'];

    /**
     * Any inline scripts needed in this section
     *
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions']
    ];
}
