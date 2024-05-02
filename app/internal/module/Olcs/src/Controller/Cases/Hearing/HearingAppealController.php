<?php

namespace Olcs\Controller\Cases\Hearing;

use Common\Service\Cqrs\Exception\NotFoundException;
use Dvsa\Olcs\Transfer\Query\Cases\Hearing\AppealByCase as AppealDto;
use Dvsa\Olcs\Transfer\Query\Cases\Hearing\StayList as StayListDto;
use Laminas\View\Model\ViewModel;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Controller\Interfaces\LeftViewProvider;

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

    /**
     * Gets left view
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
     * Ensure index action redirects to details action
     *
     * @return array|mixed|\Laminas\Http\Response|\Laminas\View\Model\ViewModel
     */
    public function indexAction()
    {
        return $this->redirectTo([]);
    }

    /**
     * Details action
     *
     * @return ViewModel
     */
    public function detailsAction()
    {
        $caseId = $this->params()->fromRoute('case');

        $this->placeholder()->setPlaceholder('caseId', $caseId);

        try {
            $appeal = $this->handleQuery(
                AppealDto::create(
                    [
                        'case' => $caseId
                    ]
                )
            );
        } catch (NotFoundException) {
            $this->placeholder()->setPlaceholder('no-appeal', true);
            return $this->viewBuilder()->buildViewFromTemplate($this->detailsViewTemplate);
        }

        $this->placeholder()->setPlaceholder('appeal', $appeal->getResult());

        $stay = $this->handleQuery(
            StayListDto::create(
                [
                    'case' => $caseId
                ]
            )
        );

        $multipleStays = $stay->getResult();

        $stayRecords = [];
        if (isset($multipleStays['results'])) {
            foreach ($multipleStays['results'] as $item) {
                $stayRecords[$item['stayType']['id']] = $item;
            }
        }

        $this->placeholder()->setPlaceholder('stays', $stayRecords);

        return $this->viewBuilder()->buildViewFromTemplate($this->detailsViewTemplate);
    }
}
