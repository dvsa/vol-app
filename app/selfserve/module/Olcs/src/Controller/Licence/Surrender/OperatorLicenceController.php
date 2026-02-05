<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Data\Mapper\Licence\Surrender\OperatorLicence as Mapper;
use Common\RefData;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Olcs\Form\Model\Form\Surrender\OperatorLicence;
use Permits\Data\Mapper\MapperManager;

class OperatorLicenceController extends AbstractSurrenderController
{
    use ReviewRedirect;

    protected $formConfig = [
        'default' => [
            'operator-licence' => [
                'formClass' => OperatorLicence::class,
                'mapper' => [
                    'class' => Mapper::class
                ],
                'dataSource' => 'surrender'
            ]
        ]
    ];

    protected $templateConfig = [
        'default' => 'licence/surrender-licence-documents'
    ];

    /**
     * @param TranslationHelperService $translationHelper
     * @param FormHelperService $formHelper
     * @param TableFactory $tableBuilder
     * @param MapperManager $mapperManager
     * @param FlashMessengerHelperService $flashMessengerHelper
     */
    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        TableFactory $tableBuilder,
        MapperManager $mapperManager,
        FlashMessengerHelperService $flashMessengerHelper
    ) {
        parent::__construct($translationHelper, $formHelper, $tableBuilder, $mapperManager, $flashMessengerHelper);
    }

    #[\Override]
    public function indexAction()
    {
        return $this->createView();
    }

    public function submitAction(): \Laminas\View\Model\ViewModel
    {
        $formData = (array)$this->getRequest()->getPost();
        $this->form->setData($formData);
        $validForm = $this->form->isValid();
        if ($validForm) {
            $data = $this->mapperManager->get(Mapper::class)->mapFromForm($formData);
            if ($this->updateSurrender(RefData::SURRENDER_STATUS_LIC_DOCS_COMPLETE, $data)) {
                $routeName = 'licence/surrender/review/GET';
                if ($this->isInternationalLicence() && $this->data['fromReview'] === false) {
                    $routeName = 'licence/surrender/community-licence/GET';
                }
                $this->nextStep($routeName);
            }
        }
        return $this->createView();
    }

    #[\Override]
    public function alterForm($form)
    {
        $form->get('form-actions')->get('submit')->setLabel($this->translationHelper->translate('lva.external.save_and_continue.button'));
        return $form;
    }


    /**
     * @return array
     */
    protected function getViewVariables(): array
    {
        return [
            'pageTitle' => 'licence.surrender.operator_licence.title',
            'licNo' => $this->data['surrender']['licence']['licNo'],
            'backUrl' => $this->getPreviousStepDetails()['returnLink'],
            'returnLinkText' => $this->getPreviousStepDetails()['returnLinkText'],
            'returnLink' => $this->getPreviousStepDetails()['returnLink'],
        ];
    }

    private function getPreviousStepDetails(): array
    {
        $previousStep = [
            'returnLinkText' => 'licence.surrender.operator_licence.return_to_current_discs.link',
            'returnLink' => $this->getLink('licence/surrender/current-discs/GET'),
        ];

        if ($this->getSurrenderStateService()->getDiscsOnSurrender() === 0) {
            $previousStep['returnLinkText'] = 'common.link.back.label';
            $previousStep['returnLink'] = $this->getLink('licence/surrender/review-contact-details/GET');
        }
        return $previousStep;
    }
}
