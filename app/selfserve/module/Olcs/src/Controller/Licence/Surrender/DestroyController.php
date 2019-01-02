<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Service\Helper\TranslationHelperService;
use Olcs\Form\Model\Form\Surrender\DeclarationSign;

class DestroyController extends AbstractSurrenderController
{
    public function indexAction()
    {
        /** @var TranslationHelperService $translator */
        $translator = $this->getServiceLocator()->get('Helper\Translation');
        $params = [
            'title' => 'licence.surrender.destroy.title',
            'licNo' => $this->licence['licNo'],
            'content' => 'markup-licence-surrender-destroy-all-licence',
            'form' => $this->getConfirmationForm($translator),
            'backLink' => $this->getBackLink('licence/surrender/review'),
        ];

        return $this->renderView($params);
    }

    public function continueAction()
    {
        return $this->redirect()->toRoute('licence/surrender/declaration', [], [], true);
    }

    private function getConfirmationForm(TranslationHelperService $translator): \Common\Form\Form
    {
        /* @var $form \Common\Form\GenericConfirmation */
        $form = $this->hlpForm->createForm('GenericConfirmation');
        $submitLabel = $translator->translate('Continue');
        $form->setSubmitLabel($submitLabel);
        $form->removeCancel();
        return $form;
    }
}
