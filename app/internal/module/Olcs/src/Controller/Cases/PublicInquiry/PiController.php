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
use Olcs\Mvc\Controller\ParameterProvider\AddFormDefaultData;
use Common\Service\Data\Sla as SlaService;

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

    protected $itemParams = ['id' => 'case'];
    protected $defaultData = ['case' => AddFormDefaultData::FROM_ROUTE];
    protected $mapperClass = PiMapper::class;
    protected $inlineScripts = ['decisionAction' => ['shared/definition'], 'slaAction' => ['pi-sla']];

    protected $redirectConfig = [
        'decision' => [
            'route' => 'case_pi',
            'action' => 'details'
        ],
        'add' => [
            'route' => 'case_pi',
            'action' => 'details'
        ],
        'edit' => [
            'route' => 'case_pi',
            'action' => 'details'
        ],
        'sla' => [
            'route' => 'case_pi',
            'action' => 'details'
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
            return $this->notFoundAction();
        }

        if ($response->isClientError() || $response->isServerError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        return $response->getResult();
    }
}
