<?php
/**
 * Note Controller
 */
namespace Olcs\Controller\Application\Processing;

use Dvsa\Olcs\Transfer\Command\Processing\Note\Create as CreateDto;
use Dvsa\Olcs\Transfer\Command\Processing\Note\Delete as DeleteDto;
use Dvsa\Olcs\Transfer\Command\Processing\Note\Update as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Processing\Note as ItemDto;
use Dvsa\Olcs\Transfer\Query\Processing\NoteList as ListDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Form\Model\Form\Note as AddForm;
use Olcs\Form\Model\Form\NoteEdit as EditForm;
use Olcs\Form\Model\Form\NoteFilter as FilterForm;
use Olcs\Data\Mapper\GenericFields as Mapper;
use Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData;
use Zend\View\Model\ViewModel;
use Olcs\Controller\Traits\NotesProcessingTrait;

/**
 * Note Controller
 */
class ApplicationProcessingNoteController extends AbstractInternalController implements
    LeftViewProvider,
    ApplicationControllerInterface
{
    use NotesProcessingTrait;

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'application_processing_notes';

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/table-comments';
    protected $defaultTableSortField = 'priority';
    protected $tableName = 'note';
    protected $listDto = ListDto::class;
    protected $listVars = ['application'];
    protected $filterForm = FilterForm::class;

    /**
     * Get method Left View
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/processing/partials/left');

        return $view;
    }

    /**
     * Variables for controlling details view rendering
     * details view and itemDto are required.
     */
    protected $detailsViewTemplate = 'pages/case/offence';
    protected $detailsViewPlaceholderName = 'details';
    protected $itemDto = ItemDto::class;
    // 'id' => 'conviction', to => from
    protected $itemParams = ['application', 'id' => 'id'];

    /**
     * Form class for add form. If this has a value, then this will be used, otherwise $formClass will be used.
     */
    protected $addFormClass = AddForm::class;

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = EditForm::class;
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = Mapper::class;

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
        'application' => AddFormDefaultData::FROM_ROUTE,
        'noteType' => 'note_t_app',
        'id' => -1,
        'version' => -1
    ];

    protected $routeIdentifier = 'id';

    /**
     * Variables for controlling the delete action.
     * Command is required, as are itemParams from above
     */
    protected $deleteCommand = DeleteDto::class;

    protected $inlineScripts = [
        'indexAction' => ['forms/filter', 'table-actions']
    ];
}
