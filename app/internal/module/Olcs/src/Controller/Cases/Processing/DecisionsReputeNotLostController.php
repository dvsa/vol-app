<?php

namespace Olcs\Controller\Cases\Processing;

use Dvsa\Olcs\Transfer\Command\TmCaseDecision\CreateReputeNotLost as CreateDto;
use Dvsa\Olcs\Transfer\Command\TmCaseDecision\Delete as DeleteDto;
use Dvsa\Olcs\Transfer\Command\TmCaseDecision\UpdateReputeNotLost as UpdateDto;
use Dvsa\Olcs\Transfer\Query\TmCaseDecision\GetByCase as ItemDto;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\TmCaseDecision as Mapper;
use Olcs\Form\Model\Form\TmCaseRepute as Form;

class DecisionsReputeNotLostController extends AbstractInternalController implements
    CaseControllerInterface,
    LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_processing_decisions';

    protected $redirectConfig = [
        'add' => [
            'route' => 'processing_decisions'
        ],
        'edit' => [
            'route' => 'processing_decisions'
        ],
        'delete' => [
            'route' => 'processing_decisions'
        ],
    ];

    /**
     * get method Left View
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
    // 'id' => 'conviction', to => from
    protected $itemParams = ['case'];

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = Form::class;
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = Mapper::class;
    protected $addContentTitle = 'Add repute not lost';
    protected $editContentTitle = 'Edit repute not lost';

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
    ];

    /**
     * Variables for controlling the delete action.
     * Command is required, as are itemParams from above
     */
    protected $deleteCommand = DeleteDto::class;

    /**
     * index action
     *
     * @return \Laminas\Http\Response
     */
    public function indexAction()
    {
        return $this->redirectToDetails();
    }

    /**
     * details Action
     *
     * @return \Laminas\Http\Response
     */
    public function detailsAction()
    {
        return $this->redirectToDetails();
    }

    /**
     * redirct to details
     *
     * @return \Laminas\Http\Response
     */
    public function redirectToDetails()
    {
        return $this->redirect()->toRouteAjax(
            'processing_decisions',
            ['action' => 'details'],
            ['code' => '303'],
            true
        );
    }
}
