<?php

namespace Olcs\Controller\IrhpPermits;

use Dvsa\Olcs\Transfer\Command\Processing\Note\Create as CreateDto;
use Dvsa\Olcs\Transfer\Command\Processing\Note\Delete as DeleteDto;
use Dvsa\Olcs\Transfer\Command\Processing\Note\Update as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Processing\Note as ItemDto;
use Dvsa\Olcs\Transfer\Query\Processing\NoteList as ListDto;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\IrhpApplicationControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits\NotesProcessingTrait;
use Olcs\Data\Mapper\GenericFields as Mapper;
use Olcs\Form\Model\Form\Note as AddForm;
use Olcs\Form\Model\Form\NoteEdit as EditForm;
use Olcs\Form\Model\Form\NoteFilter as FilterForm;
use Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData;

/**
 * Irhp Application Processing Note Controller
 */
class IrhpApplicationProcessingNoteController extends AbstractInternalController implements
    IrhpApplicationControllerInterface,
    LeftViewProvider
{
    use NotesProcessingTrait;

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'licence_irhp_applications_processing_notes';

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
    protected $listVars = ['licence'];
    protected $filterForm = FilterForm::class;

    /**
     * get method Left View
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
    protected $itemDto = ItemDto::class;
    protected $itemParams = ['licence', 'id' => 'id'];

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
    protected $addContentTitle = 'Add note';
    protected $editContentTitle = 'Edit note';

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
        'licence' => AddFormDefaultData::FROM_ROUTE,
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

    /**
     * Adds an entity
     *
     * @return ViewModel
     */
    #[\Override]
    public function addAction()
    {
        // have to overwrite the addAction() due to different naming convention
        // the param in routing is called 'irhpAppId' and the field name in the transfer is 'irhpApplication'
        $this->defaultData['irhpApplication'] = $this->params()->fromRoute('irhpAppId');

        return $this->add(
            !empty($this->addFormClass) ? $this->addFormClass : $this->formClass,
            new AddFormDefaultData($this->defaultData),
            $this->createCommand,
            $this->mapperClass,
            $this->editViewTemplate,
            $this->addSuccessMessage,
            $this->addContentTitle
        );
    }
}
