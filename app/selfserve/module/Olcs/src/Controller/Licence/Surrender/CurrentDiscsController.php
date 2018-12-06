<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Service\Helper\TranslationHelperService;
use Olcs\Form\Model\Form\Surrender\CurrentDiscs\CurrentDiscs;

class CurrentDiscsController extends AbstractSurrenderController
{
    public function indexAction()
    {
        /** @var TranslationHelperService $translator */
        $translator = $this->getServiceLocator()->get('Helper\Translation');
        $numberOfDiscs = $this->getNumberOfDiscs();

        $params = [
            'title' => 'licence.surrender.current_discs.title',
            'licNo' => $this->licence['licNo'],
            'content' => $translator->translateReplace(
                'licence.surrender.current_discs.content',
                [$numberOfDiscs]
            ),
            'form' => $this->hlpForm->createForm(CurrentDiscs::class),
            'backLink' => $this->getBackLink('licence/surrender/review-contact-details'),
        ];

        $this->getServiceLocator()->get('Script')->loadFiles(['licence-surrender-current-discs']);

        return $this->renderView($params);
    }

    protected function getNumberOfDiscs(): int
    {
        return 0;
    }
}
