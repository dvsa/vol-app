<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\FeatureToggle;
use Common\Form\Form;
use Common\Form\GenericConfirmation;
use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Command\GovUkAccount\GetGovUkAccountRedirect;
use Dvsa\Olcs\Transfer\Query\FeatureToggle\IsEnabled as IsEnabledQry;
use Laminas\Http\Response;
use Olcs\Form\Model\Form\Surrender\DeclarationSign;

class DeclarationController extends AbstractSurrenderController
{
    protected $form;

    public function indexAction()
    {
        if ($this->getRequest()->isPost()) {
            $result = $this->processSignForm();
            if ($result instanceof Response) {
                return $result;
            }
        }

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

    public function processSignForm()
    {
        $form = $this->getForm(DeclarationSign::class);
        $form->setData($this->getRequest()->getPost());
        if ($form->isValid()) {
            $data = (array) $this->getRequest()->getPost();
            if (isset($data['sign'])) {
                $featureEnabled = $this->handleQuery(IsEnabledQry::create(['ids' => [FeatureToggle::GOVUK_ACCOUNT]]))->getResult()['isEnabled'];
                if (!$featureEnabled) {
                    return $this->redirect()->toRoute(
                        'verify/surrender',
                        [
                            'licenceId' => $this->licenceId,
                        ]
                    );
                }

                $returnUrl = $this->url()->fromRoute(
                    'licence/surrender/confirmation',
                    [
                        'licence' => $this->licenceId,
                        'action' => 'index'
                    ]
                );

                $urlResult = $this->handleCommand(GetGovUkAccountRedirect::create([
                    'journey' => RefData::JOURNEY_SURRENDER,
                    'id' => $this->licenceId,
                    'returnUrl' => $returnUrl,
                ]));

                if (!$urlResult->isOk()) {
                    throw new \Exception('GetGovUkAccountRedirect command returned non-OK', $urlResult->getStatusCode());
                }

                return $this->redirect()->toUrl($urlResult->getResult()['messages'][0]);
            }
        }
    }

    protected function getSignForm(): Form
    {
        $form = $this->getForm(DeclarationSign::class);
        $form->setAttribute('action', $this->url()->fromRoute(
            'licence/surrender/declaration/sign-with-external', [], [], true
        ));

        $hasGovUkAccountError = $this->getFlashMessenger()->getContainer()->offsetExists('govUkAccountError');
        if ($hasGovUkAccountError) {
            $form->setMessages([
                'declarationsAndUndertakings' => [
                    'signatureOptions' => ['undertakings-sign-declaration-again']
                ],
            ]);
            $form->setOption('formErrorsParagraph', 'undertakings-govuk-account-generic-error');
        }

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
