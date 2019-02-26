<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Data\Mapper\Licence\Surrender\CurrentDiscs as CurrentDiscsMapper;
use Common\Form\Form;
use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Query\Licence\GoodsDiscCount;
use Dvsa\Olcs\Transfer\Query\Licence\PsvDiscCount;
use Olcs\Form\Model\Form\Surrender\CurrentDiscs\CurrentDiscs;

class CurrentDiscsController extends AbstractSurrenderController
{
    use ReviewRedirect;
    /**
     * @var Form
     */
    protected $form;

    public function indexAction()
    {
        $this->form = $this->getForm(CurrentDiscs::class);
        $formData = CurrentDiscsMapper::mapFromResult($this->data['surrender']);
        $this->form->setData($formData);

        $params = $this->getViewVariables();
        $this->getServiceLocator()->get('Script')->loadFiles(['licence-surrender-current-discs']);

        return $this->renderView($params);
    }

    public function postAction()
    {
        $this->form = $this->getForm(CurrentDiscs::class);
        $formData = (array)$this->getRequest()->getPost();
        $this->form->setData($formData);


        $validForm = $this->form->isValid();
        if (!$this->checkDiscCount($this->form->getData())) {
            $messages = $this->form->getMessages();
            $translator = $this->getServiceLocator()->get('Helper\Translation');
            $messages['headerSection']['header'] = ["disc_count_mismatch" => $translator->translate('licence.surrender.current_discs.disc_count_mismatch')];
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

        $this->hlpFlashMsgr->addUnknownError();
        $params = $this->getViewVariables();
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
        if ($this->data['surrender']['licence']['goodsOrPsv']['id'] == RefData::LICENCE_CATEGORY_GOODS_VEHICLE) {
            return $this->data['surrender']['goodsDiscsOnLicence']['discCount'];
        } else {
            return $this->data['surrender']['psvDiscsOnLicence']['discCount'];
        }
    }

    protected function checkDiscCount(array $formData): bool
    {
        $expectedDiscCount = $this->getNumberOfDiscs();
        $enteredDiscCount = $this->fetchEnteredDiscCount($formData);

        return $expectedDiscCount === $enteredDiscCount;
    }

    private function fetchEnteredDiscCount($formData): int
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
        /** @var TranslationHelperService $translator */
        $translator = $this->getServiceLocator()->get('Helper\Translation');
        $numberOfDiscs = $this->getNumberOfDiscs();
        return [
            'title' => 'licence.surrender.current_discs.title',
            'licNo' => $this->data['surrender']['licence']['licNo'],
            'content' => $translator->translateReplace(
                'licence.surrender.current_discs.content',
                [$numberOfDiscs]
            ),
            'form' => $this->form,
            'backLink' => $this->getLink('licence/surrender/review-contact-details/GET'),
            'bottomText' => 'common.link.back.label',
            'bottomLink' => $this->getLink('licence/surrender/review-contact-details/GET'),
        ];
    }
}
