<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Data\Mapper\Licence\Surrender\ReviewContactDetails;
use Common\RefData;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Olcs\Service\Surrender\SurrenderStateService;
use Permits\Data\Mapper\MapperManager;

class ReviewContactDetailsController extends AbstractSurrenderController
{
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
        return $this->renderView($this->getViewVariables());
    }

    /**
     * @return \Laminas\Http\Response|\Laminas\View\Model\ViewModel
     */
    public function postAction()
    {
        if ($this->markContactsComplete()) {
            return $this->redirect()->toRoute($this->getNextStep(), [], [], true);
        }

        $this->flashMessengerHelper->addUnknownError();

        return $this->renderView($this->getViewVariables());
    }

    protected function getViewVariables(): array
    {
        return [
            'title' => 'licence.surrender.review_contact_details.title',
            'licNo' => $this->data['surrender']['licence']['licNo'],
            'content' => 'licence.surrender.review_contact_details.content',
            'note' => 'licence.surrender.review_contact_details.note',
            'form' => $this->getConfirmationForm($this->translationHelper),
            'backLink' => $this->getLink('lva-licence'),
            'sections' => ReviewContactDetails::makeSections($this->data['surrender']['licence'], $this->url(), $this->translationHelper),
        ];
    }

    private function getConfirmationForm(TranslationHelperService $translator): \Common\Form\Form
    {
        /* @var $form \Common\Form\GenericConfirmation */
        $form = $this->formHelper->createForm('GenericConfirmation');
        $submitLabel = $translator->translate('approve-details');
        $form->setSubmitLabel($submitLabel);
        $form->removeCancel();
        return $form;
    }

    protected function markContactsComplete(): bool
    {
        return $this->updateSurrender(RefData::SURRENDER_STATUS_CONTACTS_COMPLETE);
    }

    protected function getNextStep(): string
    {
        $surrenderStateService = new SurrenderStateService();
        $surrenderStateService->setSurrenderData($this->data['surrender']);
        if ($surrenderStateService->getDiscsOnLicence() > 0) {
            return 'licence/surrender/current-discs/GET';
        }

        return 'licence/surrender/operator-licence/GET';
    }
}
