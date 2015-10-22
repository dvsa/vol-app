<?php

/**
 * Case Conviction Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller\Cases\Conviction;

use Dvsa\Olcs\Transfer\Command\Cases\Conviction\Create as CreateDto;
use Dvsa\Olcs\Transfer\Command\Cases\Conviction\Delete as DeleteDto;
use Dvsa\Olcs\Transfer\Command\Cases\Conviction\Update as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Cases\Conviction\Conviction as ItemDto;
use Dvsa\Olcs\Transfer\Query\Cases\Conviction\ConvictionList as ListDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Form\Model\Form\Conviction;
use Dvsa\Olcs\Transfer\Query\Cases\Cases as CommentItemDto;
use Dvsa\Olcs\Transfer\Command\Cases\UpdateConvictionNote as CommentUpdateDto;
use Olcs\Form\Model\Form\Comment as CommentForm;
use Olcs\Data\Mapper\ConvictionCommentBox as CommentMapper;
use Zend\View\Model\ViewModel;

/**
 * Case Conviction Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class ConvictionController extends AbstractInternalController implements CaseControllerInterface, LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_details_convictions';

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/table-comments';
    protected $defaultTableSortField = 'convictionDate';
    protected $tableName = 'conviction';
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
    protected $itemDto = ItemDto::class;
    // 'id' => 'conviction', to => from
    protected $itemParams = ['case', 'id' => 'conviction'];

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = Conviction::class;
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = \Olcs\Data\Mapper\Conviction::class;
    protected $addContentTitle = 'Add conviction';
    protected $editContentTitle = 'Edit conviction';

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
        'case' => 'route',
        'id' => -1,
        'version' => -1
    ];

    protected $routeIdentifier = 'conviction';

    /**
     * Variables for controlling the delete action.
     * Command is required, as are itemParams from above
     */
    protected $deleteCommand = DeleteDto::class;
    /**
     * Variables for controlling the delete action.
     * Format is: required => supplied
     */
    protected $deleteParams = ['id' => 'conviction'];

    protected $inlineScripts = [
        'addAction' => ['conviction'],
        'editAction' => ['conviction'],
        'indexAction' => ['table-actions']
    ];

    protected $commentFormClass = CommentForm::class;
    protected $commentItemDto = CommentItemDto::class;
    protected $commentItemParams = ['id' => 'case', 'case' => 'case'];
    protected $commentUpdateCommand = CommentUpdateDto::class;
    protected $commentMapperClass = CommentMapper::class;
}
