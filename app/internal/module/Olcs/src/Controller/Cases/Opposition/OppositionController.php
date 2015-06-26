<?php

/**
 * Case Opposition Controller
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
namespace Olcs\Controller\Cases\Opposition;

use Dvsa\Olcs\Transfer\Command\Cases\Opposition\CreateOpposition as CreateDto;
use Dvsa\Olcs\Transfer\Command\Cases\Opposition\DeleteOpposition as DeleteDto;
use Dvsa\Olcs\Transfer\Command\Cases\Opposition\UpdateOpposition as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Cases\Opposition\Opposition as ItemDto;
use Dvsa\Olcs\Transfer\Query\Cases\Opposition\OppositionList as OppositionListDto;
use Dvsa\Olcs\Transfer\Query\Cases\EnvironmentalComplaint\EnvironmentalComplaintList as EnvComplaintListDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\PageInnerLayoutProvider;
use Olcs\Controller\Interfaces\PageLayoutProvider;

/**
 * Case Opposition Controller
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class OppositionController extends AbstractInternalController implements CaseControllerInterface,
 PageLayoutProvider,
 PageInnerLayoutProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_opposition';

    protected $routeIdentifier = 'opposition';

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'partials/table';
    protected $defaultTableSortField = 'id';
    protected $tableName = 'opposition';
    protected $listDto = OppositionListDto::class;
    protected $listVars = ['case'];

    public function getPageLayout()
    {
        return 'layout/case-section';
    }

    public function getPageInnerLayout()
    {
        return 'layout/wide-layout';
    }

    /**
     * Variables for controlling details view rendering
     * details view and itemDto are required.
     */
    protected $detailsViewTemplate = 'pages/case/opposition';
    protected $detailsViewPlaceholderName = 'details';
    protected $itemDto = ItemDto::class;
    // 'id' => 'opposition', to => from
    protected $itemParams = ['case', 'id' => 'opposition'];

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = 'opposition';
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = \Olcs\Data\Mapper\Opposition::class;

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
        'addAction' => ['forms/opposition'],
        'editAction' => ['forms/opposition'],
        'deleteAction' => ['forms/opposition'],
        'indexAction' => ['table-actions']
    );

    public function detailsAction()
    {
        $this->setupOppositionDates();

        $this->setupOppositionsTable();

        //$this->setupEnvironmentComplaintsTable();

        return $this->details(
            $this->itemDto,
            $this->itemParams,
            $this->detailsViewPlaceholderName,
            $this->detailsViewTemplate
        );
    }

    private function setupOppositionDates()
    {
        $oooDate = new \DateTime();
        $oorDate = new \DateTime();

        $this->placeholder()->setPlaceholder('oorDate', $oorDate->format(\DateTime::ISO8601));
        $this->placeholder()->setPlaceholder('oooDate', $oooDate->format(\DateTime::ISO8601));
    }

    private function setupOppositionsTable()
    {
        $listDto = OppositionListDto::class;

        $paramNames = ['case'];
        $defaultSort = 'id';
        $tableViewPlaceholderName = 'oppositionsTable';
        $tableName = 'opposition';
        $tableViewTemplate = $this->tableViewTemplate;

        $this->index(
            $listDto,
            $paramNames,
            $defaultSort,
            $tableViewPlaceholderName,
            $tableName,
            $tableViewTemplate
        );
    }

    private function setupEnvironmentComplaintsTable()
    {
        $listDto = EnvComplaintListDto::class;

        $paramNames = ['case'];
        $defaultSort = 'id';
        $tableViewPlaceholderName = 'envComplaintsTable';
        $tableName = 'environmental-complaints';
        $tableViewTemplate = $this->tableViewTemplate;

        $this->index(
            $listDto,
            $paramNames,
            $defaultSort,
            $tableViewPlaceholderName,
            $tableName,
            $tableViewTemplate
        );
    }
}
