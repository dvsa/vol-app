<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Service\Helper\TranslationHelperService;
use Olcs\Form\Model\Form\Surrender\DeclarationSign;

class DeclarationController extends AbstractSurrenderController
{
    public function indexAction()
    {
        $surrender = $this->getSurrender();
        /** @var TranslationHelperService $translator */
        $translator = $this->getServiceLocator()->get('Helper\Translation');

        if ($surrender['disableSignatures'] === false) {
            $form = $this->getSignForm();
        } else {
            $form = $this->getPrintForm($translator);
        }

        $params = [
            'title' => 'licence.surrender.declaration.title',
            'licNo' => $this->licence['licNo'],
            'content' => $translator->translateReplace(
                'markup-licence-surrender-declaration',
                [$this->licence['licNo']]
            ),
            'form' => $form,
            'backLink' => $this->getBackLink('lva-licence'),
        ];

        return $this->renderView($params);
    }

    protected function getSignForm(): \Common\Form\Form
    {
        $form =  $this->getForm(DeclarationSign::class);
        $form->setAttribute(
            "action",
            $this->url()->fromRoute(
                'verify/surrender',
                [
                    'surrenderId' => $this->licenceId,
                ]
            )
        );
        return $form;
    }

    protected function getPrintForm(TranslationHelperService $translator): \Common\Form\Form
    {
        /* @var $form \Common\Form\GenericConfirmation */
        $form = $this->hlpForm->createForm('GenericConfirmation');
        $submitLabel = $translator->translate('lva.section.title.transport-manager-application.print-sign');
        $form->setSubmitLabel($submitLabel);
        $form->removeCancel();
        return $form;
    }
}
