<?php

/**
 * Case Prohibition Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller\Cases\Prohibition;

use Dvsa\Olcs\Transfer\Command\Cases\Prohibition\Defect\Create as CreateDto;
use Dvsa\Olcs\Transfer\Command\Cases\Prohibition\Defect\Delete as DeleteDto;
use Dvsa\Olcs\Transfer\Command\Cases\Prohibition\Defect\Update as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Cases\Prohibition\Defect as ItemDto;
use Dvsa\Olcs\Transfer\Query\Cases\Prohibition\DefectList as ListDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\PageInnerLayoutProvider;
use Olcs\Controller\Interfaces\PageLayoutProvider;
use Olcs\Form\Model\Form\ProhibitionDefect as Form;
use Olcs\Data\Mapper\GenericFields as Mapper;

use Dvsa\Olcs\Transfer\Query\Cases\Prohibition\Prohibition as ProhibitionDto;
use Olcs\Mvc\Controller\ParameterProvider\GenericList;

/**
 * Case Prohibition Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class ProhibitionDefectController extends AbstractInternalController implements
    CaseControllerInterface,
    PageLayoutProvider,
    PageInnerLayoutProvider
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
    protected $tableViewTemplate = 'pages/case/prohibition-defect';
    protected $defaultTableSortField = 'id';
    protected $tableName = 'prohibitionDefect';
    protected $listDto = ListDto::class;
    protected $listVars = ['prohibition'];

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
    protected $detailsViewTemplate = 'pages/case/offence';
    protected $detailsViewPlaceholderName = 'details';
    protected $itemDto = ItemDto::class;
    // 'id' => 'prohibition', to => from
    protected $itemParams = ['case', 'id' => 'id'];

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = Form::class;
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
     * see prohibition controller
     *
     * @var array
     */
    protected $defaultData = [
        'prohibition' => 'route',
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
        'indexAction' => ['table-actions'],
        'addAction' => ['table-actions'],
        'editAction' => ['table-actions']
    ];

    public function indexAction()
    {
        $prohibition = $this->params()->fromRoute('prohibition');
        $query = ProhibitionDto::create(['id' => $prohibition]);

        $response = $this->handleQuery($query);

        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

        //die('<pre>' . print_r($response->getResult(), 1));

        $this->placeholder()->setPlaceholder('prohibition', $response->getResult());

        return $this->index(
            $this->listDto,
            new GenericList($this->listVars, $this->defaultTableSortField),
            $this->tableViewPlaceholderName,
            $this->tableName,
            $this->tableViewTemplate
        );
    }
}
