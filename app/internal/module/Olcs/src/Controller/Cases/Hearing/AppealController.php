<?php

/**
 * Hearing & Appeal Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller\Cases\Hearing;

use Dvsa\Olcs\Transfer\Command\Cases\Hearing\CreateAppeal as CreateDto;
use Dvsa\Olcs\Transfer\Command\Cases\Hearing\UpdateAppeal as UpdateDto;
use Dvsa\Olcs\Transfer\Query\Cases\Hearing\Appeal as AppealDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Form\Model\Form\Appeal as FormClass;
use Olcs\Data\Mapper\Appeal as Mapper;
use Zend\View\Model\ViewModel;

/**
 * Hearing Appeal Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class AppealController extends AbstractInternalController implements CaseControllerInterface, LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_hearings_appeals_stays';

    protected $routeIdentifier = 'appeal';
    /**
     * get method for LeftView
     *
     * @return ViewModel
     */
    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/cases/partials/left');

        return $view;
    }

    protected $itemDto = AppealDto::class;
    // 'id' => 'conviction', to => from
    protected $itemParams = [
        'case',
        'id' => 'appeal',
    ];

    /**
     * Variables for controlling edit view rendering
     * all these variables are required
     * itemDto (see above) is also required.
     */
    protected $formClass = FormClass::class;
    protected $updateCommand = UpdateDto::class;
    protected $mapperClass = Mapper::class;
    protected $addContentTitle = 'Add appeal';
    protected $editContentTitle = 'Edit appeal';

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

    protected $inlineScripts = array('forms/hearings-appeal');

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
     * @return array|mixed|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        return $this->redirectTo([]);
    }
}
