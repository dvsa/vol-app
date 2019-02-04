<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Form\Form;
use Common\RefData;
use Olcs\Controller\Config\DataSource\DataSourceConfig;
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

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::SURRENDER
    ];

    /**
     * @var SurrenderStateService
     */
    private $surrenderStateService;

    /**
     * @var string
     */
    private $surrenderState;

    public function indexAction()
    {
        $this->surrenderStateService = new SurrenderStateService($this->data['surrender']);
        $this->surrenderState = $this->surrenderStateService->getState();

        if ($this->surrenderState === SurrenderStateService::STATE_OK) {
            return $this->redirect()->toRoute($this->surrenderStateService->fetchRoute(), [], [], true);
        }

        $this->form = $this->alterForm($this->form);

        return $this->createView();
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
}
