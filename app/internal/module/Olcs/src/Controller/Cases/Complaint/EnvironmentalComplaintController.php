<?php

/**
 * Case EnvironmentalComplaint Controller
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */

namespace Olcs\Controller\Cases\Complaint;

use Dvsa\Olcs\Transfer\Command\EnvironmentalComplaint\CreateEnvironmentalComplaint as CreateDto;
use Dvsa\Olcs\Transfer\Command\EnvironmentalComplaint\DeleteEnvironmentalComplaint as DeleteDto;
use Dvsa\Olcs\Transfer\Command\EnvironmentalComplaint\UpdateEnvironmentalComplaint as UpdateDto;
use Dvsa\Olcs\Transfer\Query\EnvironmentalComplaint\EnvironmentalComplaint as ItemDto;
use Laminas\View\Model\ConsoleModel;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Data\Mapper\EnvironmentalComplaint as Mapper;
use Olcs\Form\Model\Form\EnvironmentalComplaint as Form;

/**
 * Case EnvironmentalComplaint Controller
 *
 * @author S Lizzio <shaun.lizzio@valtech.co.uk>
 */
class EnvironmentalComplaintController extends AbstractInternalController implements CaseControllerInterface
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_opposition';

    protected $routeIdentifier = 'complaint';

    /**
     * @var array
     */
    protected $crudConfig = [
        'generate' => ['requireRows' => true],
    ];

    /**
     * @var array
     */
    protected $redirectConfig = [
        'add' => [
            'route' => 'case_opposition',
            'action' => 'index',
            'reUseParams' => true,
        ],
        'edit' => [
            'route' => 'case_opposition',
            'action' => 'index',
            'reUseParams' => true,
        ],
        'delete' => [
            'route' => 'case_opposition',
            'action' => 'index',
            'reUseParams' => true,
        ]
    ];

    /**
     * Variables for controlling details view rendering
     * details view and itemDto are required.
     */
    protected $itemDto = ItemDto::class;
    // 'id' => 'complaint', to => from
    protected $itemParams = ['id' => 'complaint'];

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = Form::class;
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = Mapper::class;
    protected $addContentTitle = 'Add environmental complaint';
    protected $editContentTitle = 'Edit environmental complaint';

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
    protected $deleteParams = ['id' => 'complaint'];
    protected $deleteModalTitle = 'Delete Environmental Complaint';
    /**
     * Index Action
     *
     * @return ConsoleModel|ViewModel
     */
    public function indexAction()
    {
        return $this->notFoundAction();
    }

    /**
     * details action
     *
     * @return ConsoleModel|ViewModel
     */
    public function detailsAction()
    {
        return $this->notFoundAction();
    }

    /**
     * Generate action.
     *
     * @return \Laminas\Http\Response\
     */
    public function generateAction()
    {
        return $this->redirect()->toRoute(
            'case_licence_docs_attachments/entity/generate',
            [
                'case' => $this->params()->fromRoute('case'),
                'entityType' => 'complaint',
                'entityId' => $this->params()->fromRoute('complaint')
            ]
        );
    }
}
