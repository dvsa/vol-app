<?php

namespace Olcs\Controller\Cases\Statement;

use Dvsa\Olcs\Transfer\Command\Cases\Statement\CreateStatement as CreateDto;
use Dvsa\Olcs\Transfer\Command\Cases\Statement\DeleteStatement as DeleteDto;
use Dvsa\Olcs\Transfer\Command\Cases\Statement\UpdateStatement as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Cases\Statement\Statement as ItemDto;
use Dvsa\Olcs\Transfer\Query\Cases\Statement\StatementList as ListDto;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Traits as ControllerTraits;
use Olcs\Form\Model\Form\Statement;

class StatementController extends AbstractInternalController implements CaseControllerInterface, LeftViewProvider
{
    use ControllerTraits\GenerateActionTrait;

    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_details_statements';

    protected $routeIdentifier = 'statement';

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/table-comments';
    protected $defaultTableSortField = 'id';
    protected $tableName = 'statement';
    protected $listDto = ListDto::class;
    protected $listVars = ['case'];

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
    // 'id' => 'statement', to => from
    protected $itemParams = ['case', 'id' => 'statement'];

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = Statement::class;
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = \Olcs\Data\Mapper\Statement::class;
    protected $addContentTitle = 'Add statement';
    protected $editContentTitle = 'Edit statement';

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
    protected $deleteParams = ['id' => 'statement'];
    protected $deleteModalTitle = 'internal.delete-action-trait.title';

    /**
     * Any inline scripts needed in this section
     *
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions']
    ];

    protected $crudConfig = [
        'generate' => ['requireRows' => true],
    ];

    /**
     * Route for document generate action redirects
     *
     * @see Olcs\Controller\Traits\GenerateActionTrait
     *
     * @return string
     */
    protected function getDocumentGenerateRoute()
    {
        return 'case_licence_docs_attachments/entity/generate';
    }

    /**
     * Route params for document generate action redirects
     *
     * @see Olcs\Controller\Traits\GenerateActionTrait
     *
     * @return array
     */
    protected function getDocumentGenerateRouteParams()
    {
        return [
            'case' => $this->params()->fromRoute('case'),
            'entityType' => 'statement',
            'entityId' => $this->params()->fromRoute('statement')
        ];
    }
}
