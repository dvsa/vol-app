<?php

/**
 * Case Prohibition Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller\Cases\Prohibition;

use Dvsa\Olcs\Transfer\Command\Cases\Prohibition\Create as CreateDto;
use Dvsa\Olcs\Transfer\Command\Cases\Prohibition\Delete as DeleteDto;
use Dvsa\Olcs\Transfer\Command\Cases\Prohibition\Update as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Cases\Prohibition\Prohibition as ItemDto;
use Dvsa\Olcs\Transfer\Query\Cases\Prohibition\ProhibitionList as ListDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Form\Model\Form\Prohibition as Form;
use Olcs\Data\Mapper\GenericFields as Mapper;
use Dvsa\Olcs\Transfer\Query\Cases\Cases as CommentItemDto;
use Dvsa\Olcs\Transfer\Command\Cases\UpdateProhibitionNote as CommentUpdateDto;
use Olcs\Form\Model\Form\Comment as CommentForm;
use Olcs\Data\Mapper\ProhibitionCommentBox as CommentMapper;
use Zend\View\Model\ViewModel;

/**
 * Case Prohibition Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class ProhibitionController extends AbstractInternalController implements CaseControllerInterface, LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_details_prohibitions';

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/table-comments';
    protected $defaultTableSortField = 'id';
    protected $tableName = 'prohibition';
    protected $listDto = ListDto::class;
    protected $listVars = ['case'];

     /**
     * get method left view
     *
     * @return ViewModel
     **/
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
    // 'id' => 'prohibition', to => from
    protected $itemParams = ['case', 'id' => 'prohibition'];

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = Form::class;
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = Mapper::class;
    protected $addContentTitle = 'Add prohibition';
    protected $editContentTitle = 'Edit prohibition';

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
     * see prohibition controller
     *
     * @var array
     */
    protected $defaultData = [
        'case' => 'route',
        'id' => -1,
        'version' => -1
    ];

    protected $routeIdentifier = 'prohibition';

    /**
     * Variables for controlling the delete action.
     * Command is required, as are itemParams from above
     */
    protected $deleteCommand = DeleteDto::class;
    /**
     * Variables for controlling the delete action.
     * Format is: required => supplied
     */
    protected $deleteParams = ['id' => 'prohibition'];

    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
        'addAction' => ['table-actions'],
        'editAction' => ['table-actions']
    ];

    protected $commentFormClass = CommentForm::class;
    protected $commentItemDto = CommentItemDto::class;
    protected $commentItemParams = ['id' => 'case', 'case' => 'case'];
    protected $commentUpdateCommand = CommentUpdateDto::class;
    protected $commentMapperClass = CommentMapper::class;
    protected $commentTitle = 'Prohibitions';
}
