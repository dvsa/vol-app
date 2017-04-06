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
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Form\Model\Form\ProhibitionDefect as Form;
use Olcs\Data\Mapper\GenericFields as Mapper;
use Dvsa\Olcs\Transfer\Query\Cases\Prohibition\Prohibition as ProhibitionDto;
use Olcs\Mvc\Controller\ParameterProvider\GenericList;
use Zend\View\Model\ViewModel;

/**
 * Case Prohibition Controller
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class ProhibitionDefectController extends AbstractInternalController implements
    CaseControllerInterface,
    LeftViewProvider
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
    protected $tableViewTemplate = 'sections/cases/pages/prohibition-defect';
    protected $defaultTableSortField = 'id';
    protected $tableName = 'prohibitionDefect';
    protected $listDto = ListDto::class;
    protected $listVars = ['prohibition'];

    /**
     * get method left view
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
    protected $addContentTitle = 'Add prohibition defect';
    protected $editContentTitle = 'Edit prohibition defect';

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

    /**
     * index Action
     *
     * @return array
     */
    public function indexAction()
    {
        $prohibition = $this->params()->fromRoute('prohibition');
        $query = ProhibitionDto::create(['id' => $prohibition]);

        $response = $this->handleQuery($query);

        if ($response->isNotFound()) {
            return $this->notFoundAction();
        }

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
