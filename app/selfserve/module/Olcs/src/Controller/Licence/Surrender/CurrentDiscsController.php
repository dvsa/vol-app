<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Data\Mapper\Licence\Surrender\CurrentDiscs as CurrentDiscsMapper;
use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\Surrender\Update;
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

        if ($form->isValid()) {
            $response = $this->updateSurrender($formData);
            if ($response) {
                return $this->redirect()->toRoute(
                    'licence/surrender/documentation',
                    [],
                    [],
                    true
                );
            }
        }

        $params = $this->buildViewParams($form);
        $this->getServiceLocator()->get('Script')->loadFiles(['licence-surrender-current-discs']);

        return $this->renderView($params);
    }

    protected function updateSurrender(array $formData): bool
    {
        $surrender = $this->getSurrender();
        $dtoData = [
                'id' => $this->licenceId,
                'partial' => false,
                'version' => $surrender['version'],
                'status' => RefData::SURRENDER_STATUS_DISCS_COMPLETE,

        ];

        $dtoData = array_merge($dtoData, CurrentDiscsMapper::mapFromForm($formData));
        $response = $this->handleCommand(Update::create($dtoData));

        if ($response->isOk()) {
            return true;
        }

        $this->hlpFlashMsgr->addUnknownError();
        return false;
    }

    protected function getNumberOfDiscs(): int
    {
        return 0;
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
            'backLink' => $this->getBackLink('licence/surrender/review-contact-details'),
        ];
    }
}
