<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Form\Form;
use Common\RefData;
use Common\Service\Helper\TranslationHelperService;

class DestroyController extends AbstractSurrenderController
{
    const MARKUP_ALL = 'markup-licence-surrender-destroy-all-licence';
    const MARKUP_STANDARD_INTERNATIONAL = 'markup-licence-surrender-destroy-standard-international';

    public function indexAction()
    {
        /** @var TranslationHelperService $translator */
        $translator = $this->getServiceLocator()->get('Helper\Translation');
        $params = [
            'title' => 'licence.surrender.destroy.title',
            'licNo' => $this->licence['licNo'],
            'content' => $this->getContent(),
            'form' => $this->getConfirmationForm($translator),
            'backLink' => $this->getBackLink('licence/surrender/review'),
        ];

        return $this->renderView($params);
    }

    public function continueAction()
    {
        return $this->redirect()->toRoute('licence/surrender/declaration', [], [], true);
    }

    private function getConfirmationForm(TranslationHelperService $translator): Form
    {
        /* @var $form \Common\Form\GenericConfirmation */
        $form = $this->hlpForm->createForm('GenericConfirmation');
        $submitLabel = $translator->translate('Continue');
        $form->setSubmitLabel($submitLabel);
        $form->removeCancel();
        return $form;
    }

    protected function getContent(): string
    {
        if ($this->licence['licenceType']['id'] === RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL) {
            return static::MARKUP_STANDARD_INTERNATIONAL;
        }
        return static::MARKUP_ALL;
    }
}
