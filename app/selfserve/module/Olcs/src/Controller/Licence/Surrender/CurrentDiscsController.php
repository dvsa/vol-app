<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Data\Mapper\Licence\Surrender\CurrentDiscs as CurrentDiscsMapper;
use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Query\Licence\GoodsDiscCount;
use Olcs\Form\Model\Form\Surrender\CurrentDiscs\CurrentDiscs;

class CurrentDiscsController extends AbstractSurrenderController
{
    public function indexAction()
    {
        $surrender = $this->getSurrender();

        $form = $this->getForm(CurrentDiscs::class);
        $formData = CurrentDiscsMapper::mapFromResult($surrender);
        $form->setData($formData);

        $params = $this->buildViewParams($form);
        $this->getServiceLocator()->get('Script')->loadFiles(['licence-surrender-current-discs']);

        return $this->renderView($params);
    }

    public function postAction()
    {
        $form = $this->getForm(CurrentDiscs::class);
        $formData = (array)$this->getRequest()->getPost();
        $form->setData($formData);


        $validForm = $form->isValid();
        if (!$this->checkDiscCount($form->getData())) {
            $messages = $form->getMessages();
            $translator = $this->getServiceLocator()->get('Helper\Translation');
            $messages['headerSection']['header'] = ["disc_count_mismatch" => $translator->translate('licence.surrender.current_discs.disc_count_mismatch')];
            $form->setMessages($messages);
            $validForm = false;
        }

        if ($validForm) {
            if ($this->updateDiscInfo($formData)) {
                return $this->redirect()->toRoute(
                    'licence/surrender/operator-licence',
                    [],
                    [],
                    true
                );
            }
        }

        $this->hlpFlashMsgr->addUnknownError();
        $params = $this->buildViewParams($form);
        $this->getServiceLocator()->get('Script')->loadFiles(['licence-surrender-current-discs']);

        return $this->renderView($params);
    }

    protected function updateDiscInfo(array $formData): bool
    {
        $data = CurrentDiscsMapper::mapFromForm($formData);
        return $this->updateSurrender(RefData::SURRENDER_STATUS_DISCS_COMPLETE, $data);
    }

    protected function getNumberOfDiscs(): int
    {
        $response = $this->handleQuery(
            GoodsDiscCount::create(['id' => (int)$this->params('licence')])
        );
        $result = $response->getResult();
        return $result['discCount'];
    }

    protected function buildViewParams(\Common\Form\Form $form): array
    {
        /** @var TranslationHelperService $translator */
        $translator = $this->getServiceLocator()->get('Helper\Translation');
        $numberOfDiscs = $this->getNumberOfDiscs();
        return [
            'title' => 'licence.surrender.current_discs.title',
            'licNo' => $this->licence['licNo'],
            'content' => $translator->translateReplace(
                'licence.surrender.current_discs.content',
                [$numberOfDiscs]
            ),
            'form' => $form,
            'backLink' => $this->getBackLink('licence/surrender/review-contact-details/GET'),
        ];
    }

    protected function checkDiscCount(array $formData): bool
    {
        $expectedDiscCount = $this->getNumberOfDiscs();
        $enteredDiscCount = $this->fetchEnteredDiscCount($formData);

        return $expectedDiscCount == $enteredDiscCount;
    }

    private function fetchEnteredDiscCount($formData): int
    {
        $possessionCount = $formData['possessionSection']['info']['number'] ?? 0;
        $lostCount = $formData['lostSection']['info']['number'] ?? 0;
        $stolenCount = $formData['stolenSection']['info']['number'] ?? 0;

        return $possessionCount + $lostCount + $stolenCount;
    }
}
