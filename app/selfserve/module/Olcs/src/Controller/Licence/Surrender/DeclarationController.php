<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Service\Helper\TranslationHelperService;
use Olcs\Form\Model\Form\Surrender\DeclarationSign;

class DeclarationController extends AbstractSurrenderController
{
    protected $form;

    public function indexAction()
    {
        /** @var TranslationHelperService $translator */
        $translator = $this->getServiceLocator()->get('Helper\Translation');

        if ($this->data['surrender']['disableSignatures'] === false) {
            $this->form = $this->getSignForm();
        } else {
            $this->form = $this->getPrintForm($translator);
        }

        $params = $this->getViewVariables();

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
                    'licenceId' => $this->licenceId,
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

    /**
     * @return array
     *
     */
    protected function getViewVariables(): array
    {
        /** @var TranslationHelperService $translator */
        $translator = $this->getServiceLocator()->get('Helper\Translation');
        return [
            'title' => 'licence.surrender.declaration.title',
            'licNo' => $this->data['surrender']['licence']['licNo'],
            'content' => $translator->translateReplace(
                'markup-licence-surrender-declaration',
                [$this->data['surrender']['licence']['licNo']]
            ),
            'form' => $this->form,
            'backLink' => $this->getBackLink('lva-licence'),
            'bottomLink' => $this->getBottomLinkRouteAndText()['bottomLinkRoute'],
            'bottomText' => $this->getBottomLinkRouteAndText()['bottomLinkText']
        ];
    }

    private function getBottomLinkRouteAndText()
    {
        if ($this->getSurrender()['disableSignatures'] === false) {
            return [
                'bottomLinkRoute' => $this->url()->fromRoute('licence/surrender/print-sign-return/GET', [], [], true),
                'bottomLinkText' => 'lva.section.title.transport-manager-application.print-sign'
            ];
        }

        return null;
    }
}
