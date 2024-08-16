<?php

namespace Olcs\Controller\Cases\Hearing;

use Dvsa\Olcs\Transfer\Command\Cases\Hearing\CreateStay as CreateDto;
use Dvsa\Olcs\Transfer\Command\Cases\Hearing\UpdateStay as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Cases\Hearing\Stay as StayDto;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Data\Mapper\Stay as Mapper;
use Olcs\Form\Model\Form\CaseStay as FormClass;

class StayController extends AbstractInternalController implements CaseControllerInterface, LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_hearings_appeals_stays';

    protected $routeIdentifier = 'stay';

    /**
     * get method for Left View
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
    protected $itemDto = StayDto::class;
    // 'id' => 'conviction', to => from
    protected $itemParams = [
        'case',
        'id' => 'stay',
    ];

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = FormClass::class;
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = Mapper::class;
    protected $addContentTitle = 'Add stay';
    protected $editContentTitle = 'Edit stay';

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
        'stayType' => 'route',
    ];

    protected $inlineScripts = ['forms/hearings-appeal'];

    /**
     * Allows override of default behaviour for redirects. See Case Overview Controller
     *
     * @var array
     */
    protected $redirectConfig = [
        'index' => [
            'action' => 'details',
            'route' => 'case_hearing_appeal',
            'reUseParams' => true,
        ],
        'add' => [
            'action' => 'details',
            'route' => 'case_hearing_appeal',
            'reUseParams' => true,
        ],
        'edit' => [
            'action' => 'details',
            'route' => 'case_hearing_appeal',
            'reUseParams' => true,
        ],
    ];

    /**
     * Ensure index action redirects to details action
     *
     * @return array|mixed|\Laminas\Http\Response|\Laminas\View\Model\ViewModel
     */
    public function indexAction()
    {
        return $this->redirectTo([]);
    }
}
