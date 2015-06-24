<?php

/**
 * Case ConditionUndertaking Controller
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
namespace Olcs\Controller\Cases\ConditionUndertaking;

use Dvsa\Olcs\Transfer\Command\Cases\ConditionUndertaking\CreateConditionUndertaking as CreateDto;
use Dvsa\Olcs\Transfer\Command\Cases\ConditionUndertaking\DeleteConditionUndertaking as DeleteDto;
use Dvsa\Olcs\Transfer\Command\Cases\ConditionUndertaking\UpdateConditionUndertaking as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Cases\ConditionUndertaking\ConditionUndertaking as ItemDto;
use Dvsa\Olcs\Transfer\Query\Cases\ConditionUndertaking\ConditionUndertakingList as ListDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\PageInnerLayoutProvider;
use Olcs\Controller\Interfaces\PageLayoutProvider;

/**
 * Case ConditionUndertaking Controller
 *
 * @to-do We need to extract the logic from the various LVA adapters and replicate it in a new way. This is to
 * alter the form and generate the value options for the attachedTo field which has 2 group options:
 *
 * Licence
 *     OB12345
 * OCs
 *     <oc address>
 *     <oc address>
 * 
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class ConditionUndertakingController extends AbstractInternalController implements CaseControllerInterface,
    PageLayoutProvider,
    PageInnerLayoutProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_details_conditions_undertakings';

    protected $routeIdentifier = 'id';

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/table-comments';
    protected $defaultTableSortField = 'id';
    protected $tableName = 'condition';
    protected $listDto = ListDto::class;
    protected $listVars = ['case'];

    public function getPageLayout()
    {
        return 'layout/case-section';
    }

    public function getPageInnerLayout()
    {
        return 'layout/case-details-subsection';
    }

    /**
     * Variables for controlling details view rendering
     * details view and itemDto are required.
     */
    protected $detailsViewTemplate = null;
    protected $detailsViewPlaceholderName = null;
    protected $itemDto = ItemDto::class;
    // 'id' => 'conditionUndertaking', to => from
    protected $itemParams = ['case', 'id' => 'conditionUndertaking'];

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = 'ConditionUndertaking';
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = \Olcs\Data\Mapper\ConditionUndertaking::class;

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
     * Any inline scripts needed in this section
     *
     * @var array
     */
    protected $inlineScripts = array(
        'indexAction' => ['table-actions']
    );
}
