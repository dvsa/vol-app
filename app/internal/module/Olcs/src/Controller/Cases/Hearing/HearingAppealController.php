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
use Dvsa\Olcs\Transfer\Query\Cases\Hearing\AppealByCase as AppealDto;
use Dvsa\Olcs\Transfer\Query\Cases\Hearing\StayList as StayDto;
use Dvsa\Olcs\Transfer\Query\Cases\Hearing\AppealList as ListDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
use Olcs\Form\Model\Form\Appeal as FormClass;
use Olcs\Data\Mapper\GenericFields as Mapper;
use Olcs\Logging\Log\Logger;
use Olcs\Mvc\Controller\ParameterProvider\GenericItem;
use Zend\View\Model\ViewModel;

/**
 * Hearing Appeal Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class HearingAppealController extends AbstractInternalController implements CaseControllerInterface, LeftViewProvider
{
    /**
     * Holds the navigation ID,
     * required when an entire controller is
     * represented by a single navigation id.
     */
    protected $navigationId = 'case_hearings_appeals_stays';

    /*
     * Variables for controlling table/list rendering
     * tableName and listDto are required,
     * listVars probably needs to be defined every time but will work without
     */
    protected $tableViewPlaceholderName = 'table';
    protected $tableViewTemplate = 'pages/table-comments';
    protected $defaultTableSortField = 'n/a';
    protected $tableName = 'appeal';
    protected $listDto = ListDto::class;
    protected $listVars = ['case'];

    /**
     * Variables for controlling details view rendering
     * details view and itemDto are required.
     */
    protected $detailsViewTemplate = 'sections/cases/pages/appeals-stays';
    protected $detailsViewPlaceholderName = 'appeal';
    protected $itemDto = AppealDto::class;
    // 'id' => 'conviction', to => from
    protected $itemParams = ['case'];

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

    protected $redirectConfig = [
        'add' => [
            'action' => 'details'
        ],
        'edit' => [
            'action' => 'details'
        ],
        'index' => [
            'action' => 'details'
        ]
    ];

    public function getLeftView()
    {
        $view = new ViewModel();
        $view->setTemplate('sections/cases/partials/left');

        return $view;
    }

    /**
     * Ensure index action redirects to details action
     *
     * @return array|mixed|\Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        return $this->redirectTo([]);
    }

    public function detailsAction()
    {
        Logger::debug(__FILE__);
        Logger::debug(__METHOD__);

        $this->placeholder()->setPlaceholder('case', $this->params()->fromRoute('case'));

        $paramProvider = new GenericItem($this->itemParams);
        $paramProvider->setParams($this->plugin('params'));

        $params = $paramProvider->provideParameters();
        $appealDto = AppealDto::class;
        $appealQuery = $appealDto::create($params);

        $appeal = $this->handleQuery($appealQuery);
        if ($appeal->isNotFound()) {
            $this->placeholder()->setPlaceholder('no-appeal', true);
            return $this->viewBuilder()->buildViewFromTemplate($this->detailsViewTemplate);
        }

        $this->placeholder()->setPlaceholder('appeal', $appeal->getResult());

        $stayDto = StayDto::class;
        $params = array_merge($params, ['page' => 1, 'limit' => 20, 'sort' => 'id', 'order' => 'DESC']);
        $stayQuery = $stayDto::create($params);
        $stay = $this->handleQuery($stayQuery);
        if ($stay->isNotFound()) {
            $this->placeholder()->setPlaceholder('no-stay', true);
            return $this->viewBuilder()->buildViewFromTemplate($this->detailsViewTemplate);
        }

        $multipleStays = $stay->getResult();

        $stayRecords = [];
        if (isset($multipleStays['results'])) {
            foreach ($multipleStays['results'] as $stay) {
                $stayRecords[$stay['stayType']['id']] = $stay;
            }
        }

        $this->placeholder()->setPlaceholder('stays', $stayRecords);

        return $this->viewBuilder()->buildViewFromTemplate($this->detailsViewTemplate);
    }
}
