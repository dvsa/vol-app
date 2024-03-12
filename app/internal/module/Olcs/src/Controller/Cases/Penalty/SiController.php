<?php

namespace Olcs\Controller\Cases\Penalty;

use Dvsa\Olcs\Transfer\Command\Cases\Si\CreateResponse as CreateResponseCmd;
use Dvsa\Olcs\Transfer\Command\Cases\Si\CreateSi as CreateDto;
use Dvsa\Olcs\Transfer\Command\Cases\Si\DeleteSi as DeleteDto;
use Dvsa\Olcs\Transfer\Command\Cases\Si\UpdateSi as UpdateDto;
use Dvsa\Olcs\Transfer\Command\Cases\UpdatePenaltiesNote as CommentUpdateDto;
use Dvsa\Olcs\Transfer\Query\Cases\Cases as CaseDto;
use Dvsa\Olcs\Transfer\Query\Cases\Si\Si as ItemDto;
use Dvsa\Olcs\Transfer\Query\Cases\Si\SiList as ListDto;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\GenericFields;
use Olcs\Data\Mapper\PenaltyCommentBox as CommentMapper;
use Olcs\Form\Model\Form\Comment as CommentForm;
use Olcs\Form\Model\Form\Si as Form;
use Olcs\Mvc\Controller\ParameterProvider\GenericItem;

class SiController extends AbstractInternalController implements CaseControllerInterface, LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_details_penalties';

    /**
     * Any inline scripts needed in this section
     *
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions']
    ];

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/table-comments';
    protected $tableName = 'serious-infringement';
    protected $listDto = ListDto::class;
    protected $listVars = ['case'];

    protected $crudConfig = [
        'send' => ['requireRows' => false]
    ];

    /**
     * Get left view
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

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = Form::class;
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = GenericFields::class;

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

    // comment
    protected $commentFormClass = CommentForm::class;
    protected $commentItemDto = CaseDto::class;
    protected $commentItemParams = ['id' => 'case'];
    protected $commentUpdateCommand = CommentUpdateDto::class;
    protected $commentMapperClass = CommentMapper::class;

    /**
     * Index action
     *
     * @return HttpResponse
     */
    public function indexAction()
    {
        $case = $this->handleQuery(CaseDto::create(['id' => $this->params()->fromRoute('case')]))->getResult();

        if ($case['isErru']) {
            // use different table / view template for ERRU
            $this->tableName = 'erru-si';
            $this->tableViewTemplate = 'sections/cases/pages/erru-si';
        }

        return parent::indexAction();
    }

    /**
     * Sends the response back to Erru
     *
     * @return HttpResponse
     */
    public function sendAction()
    {
        return $this->confirmCommand(
            new GenericItem(['case' => 'case']),
            CreateResponseCmd::class,
            'Send response',
            'Are you sure you want to send the response?',
            'Response created and queued for sending'
        );
    }
}
