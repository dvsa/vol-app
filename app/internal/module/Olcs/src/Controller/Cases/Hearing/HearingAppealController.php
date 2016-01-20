<?php

/**
 * Hearing & Appeal Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace Olcs\Controller\Cases\Hearing;

use Dvsa\Olcs\Transfer\Query\Cases\Hearing\AppealByCase as AppealDto;
use Dvsa\Olcs\Transfer\Query\Cases\Hearing\StayList as StayDto;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;
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

    /**
     * Variables for controlling details view rendering
     * details view and itemDto are required.
     */
    protected $detailsViewTemplate = 'sections/cases/pages/appeals-stays';
    protected $detailsViewPlaceholderName = 'appeal';
    // 'id' => 'conviction', to => from
    protected $itemParams = ['case'];

    protected $redirectConfig = [
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
