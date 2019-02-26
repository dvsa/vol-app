<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Form\Form;
use Common\Form\GenericConfirmation;
use Common\RefData;
use Common\Service\Helper\TranslationHelperService;

class DestroyController extends AbstractSurrenderController
{
    const MARKUP_ALL = 'markup-licence-surrender-destroy-all-licence';
    const MARKUP_STANDARD_INTERNATIONAL = 'markup-licence-surrender-destroy-standard-international';

    public function indexAction()
    {
        $params = $this->getViewVariables();
        return $this->renderView($params);
    }

    public function continueAction()
    {
        return $this->redirect()->toRoute('licence/surrender/declaration/GET', [], [], true);
    }

    private function getConfirmationForm(TranslationHelperService $translator): Form
    {
        /* @var $form GenericConfirmation */
        $form = $this->hlpForm->createForm('GenericConfirmation');
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
        /** @var TranslationHelperService $translator */
        $translator = $this->getServiceLocator()->get('Helper\Translation');
        return [
            'title' => 'licence.surrender.destroy.title',
            'licNo' => $this->data['surrender']['licence']['licNo'],
            'content' => $this->getContent(),
            'form' => $this->getConfirmationForm($translator),
            'backLink' => $this->getLink('licence/surrender/review/GET'),
        ];
    }
}
