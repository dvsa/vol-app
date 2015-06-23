<?php

/**
 * Case Impounding Controller
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
namespace Olcs\Controller\Cases\Impounding;

use Dvsa\Olcs\Transfer\Command\Cases\Impounding\CreateImpounding as CreateDto;
use Dvsa\Olcs\Transfer\Command\Cases\Impounding\DeleteImpounding as DeleteDto;
use Dvsa\Olcs\Transfer\Command\Cases\Impounding\UpdateImpounding as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Cases\Impounding\Impounding as ItemDto;
use Dvsa\Olcs\Transfer\Query\Cases\Impounding\ImpoundingList as ListDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\PageInnerLayoutProvider;
use Olcs\Controller\Interfaces\PageLayoutProvider;

/**
 * Case Impounding Controller
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class ImpoundingController extends AbstractInternalController
    implements CaseControllerInterface,
    PageLayoutProvider,
    PageInnerLayoutProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_details_impounding';

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/table-comments';
    protected $defaultTableSortField = 'id';
    protected $tableName = 'impounding';
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
    protected $detailsViewTemplate = 'pages/case/impounding';
    protected $detailsViewPlaceholderName = 'details';
    protected $itemDto = ItemDto::class;
    // 'id' => 'impounding', to => from
    protected $itemParams = ['case', 'id' => 'impounding'];

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = 'impounding';
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = \Olcs\Data\Mapper\Impounding::class;

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
        'addAction' => ['forms/impounding'],
        'editAction' => ['forms/impounding'],
        'deleteAction' => ['forms/impounding'],
        'indexAction' => ['table-actions']
    );

    /**
     * @return \Zend\Http\Response
     */
    protected function redirectToIndex()
    {
        return $this->redirect()->toRouteAjax(
            'case_details_impounding',
            ['action' => 'index', 'impounding' => null], // ID Not required for index.
            ['code' => '303'], // Why? No cache is set with a 303 :)
            true
        );
    }
}
