<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Form\Form;
use Common\RefData;
use Dvsa\Olcs\Transfer\Command\Surrender\Create;
use Dvsa\Olcs\Transfer\Command\Surrender\Delete;
use Olcs\Form\Model\Form\Surrender\InformationChanged;
use Olcs\Service\Surrender\SurrenderStateService;

class InformationChangedController extends AbstractSurrenderController
{

    protected $formConfig = [
        'index' => [
            'continueForm' => [
                'formClass' => InformationChanged::class,
            ]
        ]
    ];

    protected $templateConfig = [
        'default' => 'licence/surrender-information-changed'
    ];

    /**
     * @var SurrenderStateService
     */
    private $surrenderStateService;

    /**
     * @var string
     */
    private $surrenderState;

    public function __construct()
    {
        $this->surrenderStateService = new SurrenderStateService();
    }

    public function indexAction()
    {
        $this->surrenderState = $this->surrenderStateService->setSurrenderData($this->data['surrender'])->getState();

        if ($this->surrenderState === SurrenderStateService::STATE_OK) {
            return $this->redirect()->toRoute($this->surrenderStateService->fetchRoute(), [], [], true);
        }

        $this->form = $this->alterForm($this->form);

        return $this->createView();
    }

    public function submitAction()
    {
        if ($this->surrenderStateService->setSurrenderData($this->data['surrender'])->hasExpired()) {
            if (!$this->deleteSurrender() || !$this->createSurrender()) {
                $this->hlpFlashMsgr->addUnknownError();
                return $this->redirect()->refresh();
            }
        }

        return $this->redirect()->toRoute('licence/surrender/review-contact-details/GET', [], [], true);
    }

    protected function getViewVariables(): array
    {
        $licenceType = $this->data['surrender']['licence']['goodsOrPsv']['id'] == RefData::LICENCE_CATEGORY_GOODS_VEHICLE ? 'gv' : 'psv';

        return [
            'pageTitle' => 'licence.surrender.information_changed.heading.' . $licenceType,
            'licNo' => $this->data['surrender']['licence']['licNo'],
            'content' => $this->getContent(),
            'backUrl' => $this->getBackLink('lva-licence')
        ];
    }

    protected function getContent(): string
    {
        if ($this->hasApplicationExpired()) {
            return 'markup-licence-surrender-information-changed-content-expired';
        }

        if ($this->hasInformationChanged()) {
            return 'markup-licence-surrender-information-changed-content-changed';
        }
    }

    public function alterForm($form)
    {
        if ($this->hasApplicationExpired()) {
            $form = $this->alterForExpiry($form);
        } elseif ($this->hasInformationChanged()) {
            $form = $this->alterForInformationChanged($form);
        }
        return $form;
    }

    protected function alterForExpiry(Form $form): Form
    {
        $form->remove('reviewAndContinue');
        return $form;
    }

    protected function alterForInformationChanged(Form $form): Form
    {
        $form->remove('startAgain');
        return $form;
    }

    protected function hasApplicationExpired(): bool
    {
        return $this->surrenderState === SurrenderStateService::STATE_EXPIRED;
    }

    protected function hasInformationChanged(): bool
    {
        return $this->surrenderState === SurrenderStateService::STATE_INFORMATION_CHANGED;
    }

    protected function deleteSurrender()
    {
        try {
            $response = $this->handleCommand(Delete::create(['id' => $this->licenceId]));
            return $response->isOk();
        } catch (\Exception $e) {
            return false;
        }
    }

    protected function createSurrender()
    {
        try {
            $response = $this->handleCommand(Create::create(['id' => $this->licenceId]));
            if ($response->isOk()) {
                $result = $response->getResult();
                if (!empty($result)) {
                    return true;
                }
            }
        } catch (\Exception $e) {
            return false;
        }
    }
}
