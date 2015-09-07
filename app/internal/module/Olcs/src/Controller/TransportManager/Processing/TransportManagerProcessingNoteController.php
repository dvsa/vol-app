<?php
/**
 * Note Controller
 */
namespace Olcs\Controller\TransportManager\Processing;

use Dvsa\Olcs\Transfer\Command\Processing\Note\Create as CreateDto;
use Dvsa\Olcs\Transfer\Command\Processing\Note\Delete as DeleteDto;
use Dvsa\Olcs\Transfer\Command\Processing\Note\Update as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Processing\Note as ItemDto;
use Dvsa\Olcs\Transfer\Query\Processing\NoteList as ListDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\TransportManagerControllerInterface;
use Olcs\Controller\Interfaces\PageInnerLayoutProvider;
use Olcs\Controller\Interfaces\PageLayoutProvider;
use Olcs\Form\Model\Form\Note as AddForm;
use Olcs\Form\Model\Form\NoteEdit as EditForm;
use Olcs\Form\Model\Form\NoteFilter as FilterForm;
use Olcs\Data\Mapper\GenericFields as Mapper;
use Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData;

/**
 * Note Controller
 */
class TransportManagerProcessingNoteController extends AbstractInternalController implements
    TransportManagerControllerInterface,
    PageLayoutProvider,
    PageInnerLayoutProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'transport_manager_processing_notes';

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
    protected $listVars = [
        'transportManager' => 'transportManager'
    ];
    protected $filterForm = FilterForm::class;

    public function getPageLayout()
    {
        return 'layout/transport-manager-section-crud';
    }

    public function getPageInnerLayout()
    {
        return 'layout/transport-manager-subsection';
    }

    /**
     * Variables for controlling details view rendering
     * details view and itemDto are required.
     */
    protected $detailsViewTemplate = 'pages/case/offence';
    protected $detailsViewPlaceholderName = 'details';
    protected $itemDto = ItemDto::class;
    // 'id' => 'conviction', to => from
    protected $itemParams = [
        'transportManager' => 'transportManager',
        'id'
    ];

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
        'transportManager' => \Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData::FROM_ROUTE,
        'noteType' => 'note_t_tm',
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
