<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Data\Mapper\Licence\Surrender\CurrentDiscs as CurrentDiscsMapper;
use Common\Form\Form;
use Common\RefData;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Common\Service\Table\TableFactory;
use Olcs\Form\Model\Form\Surrender\CurrentDiscs\CurrentDiscs;
use Permits\Data\Mapper\MapperManager;

class CurrentDiscsController extends AbstractSurrenderController
{
    use ReviewRedirect;

    /**
     * @var Form
     */
    protected $form;

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
        FlashMessengerHelperService $flashMessengerHelper,
        protected ScriptFactory $scriptFactory
    ) {
        parent::__construct($translationHelper, $formHelper, $tableBuilder, $mapperManager, $flashMessengerHelper);
    }

    #[\Override]
    public function indexAction()
    {
        $this->skip();
        $this->form = $this->getForm(CurrentDiscs::class);
        $formData = CurrentDiscsMapper::mapFromResult($this->data['surrender']);
        $this->form->setData($formData);

        $params = $this->getViewVariables();
        $this->scriptFactory->loadFiles(['licence-surrender-current-discs']);

        return $this->renderView($params);
    }

    /**
     * @return \Laminas\Http\Response|\Laminas\View\Model\ViewModel
     */
    public function postAction()
    {
        $this->form = $this->getForm(CurrentDiscs::class);
        $formData = (array)$this->getRequest()->getPost();
        $this->form->setData($formData);

        $validForm = $this->form->isValid();
        if (!$this->checkDiscCount($this->form->getData())) {
            $messages = $this->form->getMessages();
            $messages['headerSection']['header'] = ["disc_count_mismatch" => $this->translationHelper->translate('licence.surrender.current_discs.disc_count_mismatch')];
            $this->form->setMessages($messages);
            $validForm = false;
        }

        if ($validForm) {
            if ($this->updateDiscInfo($formData)) {
                $nextStep = 'licence/surrender/operator-licence/GET';
                if ($this->data['fromReview']) {
                    $nextStep = 'licence/surrender/review/GET';
                }
                return $this->nextStep($nextStep);
            }
        }

        $this->flashMessengerHelper->addUnknownError();
        $params = $this->getViewVariables();
        $this->scriptFactory->loadFiles(['licence-surrender-current-discs']);

        return $this->renderView($params);
    }

    protected function updateDiscInfo(array $formData): bool
    {
        $data = CurrentDiscsMapper::mapFromForm($formData);
        return $this->updateSurrender(RefData::SURRENDER_STATUS_DISCS_COMPLETE, $data);
    }

    protected function checkDiscCount(array $formData): bool
    {
        $expectedDiscCount = $this->getNumberOfDiscs();
        $enteredDiscCount = $this->fetchEnteredDiscCount($formData);

        return $expectedDiscCount === $enteredDiscCount;
    }

    private function fetchEnteredDiscCount(array $formData): int
    {
        $data = CurrentDiscsMapper::mapFromForm($formData);

        $discDestroyed = $data['discDestroyed'] ?? 0;
        $discLost = $data['discLost'] ?? 0;
        $discStolen = $data['discStolen'] ?? 0;

        return $discDestroyed + $discLost + $discStolen;
    }

    /**
     * @return array
     *
     */
    protected function getViewVariables(): array
    {
        $numberOfDiscs = $this->getNumberOfDiscs();
        return [
            'title' => 'licence.surrender.current_discs.title',
            'licNo' => $this->data['surrender']['licence']['licNo'],
            'content' => $this->translationHelper->translateReplace(
                'licence.surrender.current_discs.content',
                [$numberOfDiscs]
            ),
            'form' => $this->form,
            'backLink' => $this->getLink('licence/surrender/review-contact-details/GET'),
            'bottomText' => 'common.link.back.label',
            'bottomLink' => $this->getLink('licence/surrender/review-contact-details/GET'),
        ];
    }

    private function skip(): void
    {
        if ($this->getNumberOfDiscs() === 0) {
            $this->nextStep('licence/surrender/operator-licence/GET');
        }
    }
}
