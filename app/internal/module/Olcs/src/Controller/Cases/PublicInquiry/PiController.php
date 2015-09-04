<?php

namespace Olcs\Controller\Cases\PublicInquiry;

use Olcs\Controller\Interfaces\PageInnerLayoutProvider;
use Olcs\Controller\Interfaces\PageLayoutProvider;
use Olcs\Controller\AbstractInternalController;
use Olcs\Controller\Interfaces\CaseControllerInterface;
use Olcs\Data\Mapper\Pi as PiMapper;
use Dvsa\Olcs\Transfer\Query\Cases\Pi as PiDto;
use Olcs\Form\Model\Form\PublicInquiryRegisterDecision as DecisionForm;
use Olcs\Form\Model\Form\PublicInquiryRegisterTmDecision as TmDecisionForm;
use Olcs\Form\Model\Form\PublicInquirySla as SlaForm;
use Olcs\Form\Model\Form\PublicInquiryAgreedAndLegislation as AgreedAndLegislationForm;
use Olcs\Mvc\Controller\ParameterProvider\GenericItem;
use Dvsa\Olcs\Transfer\Command\Cases\Pi\CreateAgreedAndLegislation as CreateCmd;
use Dvsa\Olcs\Transfer\Command\Cases\Pi\UpdateAgreedAndLegislation as UpdateCmd;
use Dvsa\Olcs\Transfer\Command\Cases\Pi\UpdateDecision as UpdateDecisionCmd;
use Dvsa\Olcs\Transfer\Command\Cases\Pi\UpdateSla as UpdateSlaCmd;
use Dvsa\Olcs\Transfer\Command\Cases\Pi\Close as CloseCmd;
use Dvsa\Olcs\Transfer\Command\Cases\Pi\Reopen as ReopenCmd;
use Common\Service\Data\Sla as SlaService;
use Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData;

/**
 * Class PiController
 */
class PiController extends AbstractInternalController implements
    CaseControllerInterface,
    PageLayoutProvider,
    PageInnerLayoutProvider
{
    /** Details view */
    protected $detailsViewPlaceholderName = 'pi';
    protected $detailsViewTemplate = 'pages/case/public-inquiry';
    protected $navigationId = 'case_hearings_appeals_public_inquiry';
    protected $itemDto = PiDto::class;

    /** Create and update Pi with Agreed and Legislation info */
    protected $createCommand = CreateCmd::class;
    protected $updateCommand = UpdateCmd::class;
    protected $formClass = AgreedAndLegislationForm::class;

    /** Pi Decision */
    protected $updateDecisionCommand = UpdateDecisionCmd::class;
    protected $decisionForm = DecisionForm::class;

    /** Sla */
    protected $slaForm = SlaForm::class;
    protected $updateSlaCommand = UpdateSlaCmd::class;

    /** Close */
    protected $closeCommand = CloseCmd::class;
    protected $closeParams = ['id' => 'case'];
    protected $closeModalTitle = 'Close the Pi';
    protected $closeConfirmMessage = 'Are you sure you want to close the Pi?';
    protected $closeSuccessMessage = 'Pi closed';

    /** Reopen */
    protected $reopenCommand = ReopenCmd::class;
    protected $reopenParams = ['id' => 'case'];
    protected $reopenModalTitle = 'Reopen the Pi?';
    protected $reopenConfirmMessage = 'Are you sure you want to reopen the Pi?';
    protected $reopenSuccessMessage = 'Pi reopened';

    protected $itemParams = ['id' => 'case'];
    protected $defaultData = ['case' => AddFormDefaultData::FROM_ROUTE];
    protected $mapperClass = PiMapper::class;
    protected $inlineScripts = ['decisionAction' => ['shared/definition'], 'slaAction' => ['pi-sla']];

    protected $redirectConfig = [
        'decision' => [
            'route' => 'case_pi',
            'action' => 'index'
        ],
        'add' => [
            'route' => 'case_pi',
            'action' => 'index'
        ],
        'edit' => [
            'route' => 'case_pi',
            'action' => 'index'
        ],
        'sla' => [
            'route' => 'case_pi',
            'action' => 'index'
        ]
    ];

    public function getPageInnerLayout()
    {
        return 'layout/case-details-subsection';
    }

    public function getPageLayout()
    {
        return 'layout/case-section';
    }

    public function indexAction()
    {
        $pi = $this->getPi();

        if (isset($pi['id'])) {
            if ($this->getRequest()->isPost()) {
                $action = strtolower($this->params()->fromPost('action'));
                $id = $this->params()->fromPost('id');

                if (!($action == 'edit' && !is_numeric($id))) {
                    //if we have an add action make sure there's no row selected
                    if ($action == 'add') {
                        $id = null;
                    }

                    return $this->redirect()->toRouteAjax(
                        'case_pi_hearing',
                        ['action' => $action, 'id' => $id, 'pi' => $pi['id']],
                        ['code' => '303'], // Why? No cache is set with a 303 :)
                        true
                    );
                }
            }

            $this->forward()->dispatch(
                'PublicInquiry\HearingController',
                array(
                    'action' => 'index',
                    'case' => $pi['case']['id'],
                    'pi' => $pi['id']
                )
            );

            return $this->details(
                $this->itemDto,
                new GenericItem($this->itemParams),
                $this->detailsViewPlaceholderName,
                $this->detailsViewTemplate
            );
        }

        return $this->viewBuilder()->buildViewFromTemplate($this->detailsViewTemplate);
    }

    /**
     * Deal with Pi decisions
     *
     * @return array|\Zend\View\Model\ViewModel
     */
    public function decisionAction()
    {
        $pi = $this->getPi();

        if ($pi['isTm']) {
            $this->decisionForm = TmDecisionForm::class;
        }

        return $this->edit(
            $this->decisionForm,
            $this->itemDto,
            new GenericItem($this->itemParams),
            $this->updateDecisionCommand,
            $this->mapperClass
        );
    }

    /**
     * Deal with Pi Sla
     *
     * @return array|\Zend\View\Model\ViewModel
     */
    public function slaAction()
    {
        $pi = $this->getPi();
        $this->getServiceLocator()->get(SlaService::class)->setContext('pi', $pi);

        return $this->edit(
            $this->slaForm,
            $this->itemDto,
            new GenericItem($this->itemParams),
            $this->updateSlaCommand,
            $this->mapperClass
        );
    }

    /**
     * Gets Pi information
     *
     * @return array|mixed
     */
    private function getPi()
    {
        $params = ['id' => $this->params()->fromRoute('case')];
        $response = $this->handleQuery(PiDto::create($params));

        if ($response->isNotFound()) {
            $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            //don't display error for pi not foound on index, as it shouldn't necessarily have one
            $action = $this->getEvent()->getRouteMatch()->getParam('action');
            if ($action !== 'index') {
                $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
            }
        }

        return $response->getResult();
    }
}
