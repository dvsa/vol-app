<?php

namespace Olcs\Controller\Licence\Surrender;

use Olcs\Form\Model\Form\Surrender\DeclarationSign;

class DeclarationController extends AbstractSurrenderController
{
    public function indexAction()
    {
        $surrender = $this->getSurrender();

        if ($surrender['disableSignatures'] === false) {
            $form = $this->getSignForm();
        } else {
            $form = $this->getPrintForm();
        }

        $params = [
            'title' => 'licence.surrender.declaration.title',
            'licNo' => $this->licence['licNo'],
            'content' => [
                'markup' => 'markup-licence-surrender-declaration',
                'data' => [$this->licence['licNo']]
            ],
            'form' => $form,
            'backLink' => $this->getBackLink('lva-licence'),
        ];

        return $this->renderView($params);
    }

    protected function getSignForm(): \Common\Form\Form
    {
        return $this->getForm(DeclarationSign::class);
    }

    protected function getPrintForm(): \Common\Form\Form
    {
        /** @var TranslationHelperService $translator */
        $translator = $this->getServiceLocator()->get('Helper\Translation');

        /* @var $form \Common\Form\GenericConfirmation */
        $form = $this->hlpForm->createForm('GenericConfirmation');
        $submitLabel = $translator->translate('lva.section.title.transport-manager-application.print-sign');
        $form->setSubmitLabel($submitLabel);
        $form->removeCancel();
        return $form;
    }
}
