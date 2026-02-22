<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Form\Form;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Permits\Data\Mapper\MapperManager;

class DestroyController extends AbstractSurrenderController
{
    public const MARKUP_ALL = 'markup-licence-surrender-destroy-all-licence';
    public const MARKUP_STANDARD_INTERNATIONAL = 'markup-licence-surrender-destroy-standard-international';

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
        $params = $this->getViewVariables();
        return $this->renderView($params);
    }

    public function continueAction(): \Laminas\Http\Response
    {
        return $this->redirect()->toRoute('licence/surrender/declaration/GET', [], [], true);
    }

    private function getConfirmationForm(TranslationHelperService $translator): Form
    {
        /* @var $form GenericConfirmation */
        $form = $this->formHelper->createForm('GenericConfirmation');
        $submitLabel = $translator->translate('Continue');
        $form->setSubmitLabel($submitLabel);
        $form->removeCancel();
        return $form;
    }

    protected function getContent(): string
    {
        if ($this->isInternationalLicence()) {
            return static::MARKUP_STANDARD_INTERNATIONAL;
        }
        return static::MARKUP_ALL;
    }

    /**
     * @return array
     *
     */
    protected function getViewVariables(): array
    {
        return [
            'title' => 'licence.surrender.destroy.title',
            'licNo' => $this->data['surrender']['licence']['licNo'],
            'content' => $this->getContent(),
            'form' => $this->getConfirmationForm($this->translationHelper),
            'backLink' => $this->getLink('licence/surrender/review/GET'),
        ];
    }
}
