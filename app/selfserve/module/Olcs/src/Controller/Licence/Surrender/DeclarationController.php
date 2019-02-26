<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\Form\Form;
use Common\Form\GenericConfirmation;
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

    protected function getSignForm(): Form
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

    protected function getPrintForm(TranslationHelperService $translator): Form
    {
        /* @var $form GenericConfirmation */
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
            'backLink' => $this->getLink('lva-licence'),
            'bottomLink' => $this->getBottomLinkRouteAndText()['bottomLinkRoute'],
            'bottomText' => $this->getBottomLinkRouteAndText()['bottomLinkText']
        ];
    }

    private function getBottomLinkRouteAndText()
    {
        if ($this->data['surrender']['disableSignatures'] === false) {
            return [
                'bottomLinkRoute' => $this->getLink('licence/surrender/print-sign-return/GET'),
                'bottomLinkText' => 'lva.section.title.transport-manager-application.print-sign'
            ];
        }

        return null;
    }
}
