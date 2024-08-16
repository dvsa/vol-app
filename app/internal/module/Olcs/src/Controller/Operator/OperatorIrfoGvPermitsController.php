<?php

namespace Olcs\Controller\Operator;

use Common\Rbac\Service\Permission;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\Irfo\ApproveIrfoGvPermit as ApproveDto;
use Dvsa\Olcs\Transfer\Command\Irfo\CreateIrfoGvPermit as CreateDto;
use Dvsa\Olcs\Transfer\Command\Irfo\GenerateIrfoGvPermit as GenerateDto;
use Dvsa\Olcs\Transfer\Command\Irfo\RefuseIrfoGvPermit as RefuseDto;
use Dvsa\Olcs\Transfer\Command\Irfo\ResetIrfoGvPermit as ResetDto;
use Dvsa\Olcs\Transfer\Command\Irfo\WithdrawIrfoGvPermit as WithdrawDto;
use Dvsa\Olcs\Transfer\Query\Irfo\IrfoGvPermit as ItemDto;
use Dvsa\Olcs\Transfer\Query\Irfo\IrfoGvPermitList as ListDto;
use Laminas\Navigation\Navigation;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Controller\Interfaces\OperatorControllerInterface;
use Olcs\Data\Mapper\IrfoGvPermit as Mapper;
use Olcs\Form\Model\Form\IrfoGvPermit as Form;
use Olcs\Mvc\Controller\ParameterProvider\GenericItem;

class OperatorIrfoGvPermitsController extends AbstractInternalController implements
    OperatorControllerInterface,
    LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'operator_irfo_gv_permits';

    /**
     * @var array
     */
    protected $inlineScripts = [
        'indexAction' => ['table-actions'],
        'addAction' => ['forms/irfo-gv-permit'],
    ];

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/table-comments';
    protected $tableName = 'operator.irfo.gv-permits';
    protected $listDto = ListDto::class;
    protected $listVars = ['organisation'];


    public function __construct(
        TranslationHelperService $translationHelperService,
        FormHelperService $formHelperService,
        FlashMessengerHelperService $flashMessengerHelperService,
        Navigation $navigation,
        private Permission $permissionService
    ) {
        parent::__construct($translationHelperService, $formHelperService, $flashMessengerHelperService, $navigation);
    }

    /**
     * get Method leftView
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/operator/partials/left');

        return $view;
    }

    /**
     * Variables for controlling details view rendering
     * details view and itemDto are required.
     */
    protected $detailsViewTemplate = 'sections/operator/pages/irfo-gv-permit';
    protected $itemDto = ItemDto::class;
    protected $detailsContentTitle = 'GV Permits';

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = Form::class;
    protected $mapperClass = Mapper::class;
    protected $addContentTitle = 'Add IRFO GV Permit';

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
        'organisation' => 'route',
        'irfoPermitStatus' => 'irfo_perm_s_pending'
    ];

    public function detailsAction()
    {
        $this->placeholder()->setPlaceholder('isInternalReadOnly', $this->permissionService->isInternalReadOnly());
        return parent::detailsAction();
    }

    /**
     * not found action
     *
     * @return array
     */
    public function editAction()
    {
        return $this->notFoundAction();
    }

    /**
     * not found action
     *
     * @return array
     */
    public function deleteAction()
    {
        return $this->notFoundAction();
    }

    /**
     * reset action
     *
     * @return \Laminas\Http\Response
     */
    public function resetAction()
    {
        return $this->processCommand(new GenericItem(['id' => 'id']), ResetDto::class);
    }

    /**
     * approve action
     *
     * @return \Laminas\Http\Response
     */
    public function approveAction()
    {
        return $this->processCommand(new GenericItem(['id' => 'id']), ApproveDto::class);
    }

    /**
     * withdraw action
     *
     * @return \Laminas\Http\Response
     */
    public function withdrawAction()
    {
        return $this->processCommand(new GenericItem(['id' => 'id']), WithdrawDto::class);
    }

    /**
     * refuse action
     *
     * @return \Laminas\Http\Response
     */
    public function refuseAction()
    {
        return $this->processCommand(new GenericItem(['id' => 'id']), RefuseDto::class);
    }

    /**
     * generate action
     *
     * @return \Laminas\Http\Response
     */
    public function generateAction()
    {
        return $this->processCommand(
            new GenericItem(['id' => 'id']),
            GenerateDto::class,
            'IRFO GV Permit generated successfully'
        );
    }
}
