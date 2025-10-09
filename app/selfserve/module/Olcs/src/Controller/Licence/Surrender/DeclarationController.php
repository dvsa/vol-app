<?php

namespace Olcs\Controller\Licence\Surrender;

use Common\FeatureToggle;
use Common\Form\Form;
use Common\Form\GenericConfirmation;
use Common\RefData;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\GovUkAccount\GetGovUkAccountRedirect;
use Dvsa\Olcs\Transfer\Query\FeatureToggle\IsEnabled as IsEnabledQry;
use Laminas\Http\Response;
use Olcs\Form\Model\Form\Surrender\DeclarationSign;
use Permits\Data\Mapper\MapperManager;

class DeclarationController extends AbstractSurrenderController
{
    protected $form;

    /**
     * @param TranslationHelperService $translationHelper
     * @param FormHelperService $formHelper
     * @param TableFactory $tableBuilder
     * @param MapperManager $mapperManager
     * @param FlashMessengerHelperService $flashMessengerHelper
     */
    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        TableFactory $tableBuilder,
        MapperManager $mapperManager,
        FlashMessengerHelperService $flashMessengerHelper
    ) {
        parent::__construct($translationHelper, $formHelper, $tableBuilder, $mapperManager, $flashMessengerHelper);
    }

    /**
     * @return Response|\Laminas\View\Model\ViewModel
     */
    public function indexAction()
    {
        if ($this->getRequest()->isPost()) {
            $result = $this->processSignForm();
            if ($result instanceof Response) {
                return $result;
            }
        }

        if ($this->data['surrender']['disableSignatures'] === false) {
            $this->form = $this->getSignForm();
        } else {
            $this->form = $this->getPrintForm($this->translationHelper);
        }

        $params = $this->getViewVariables();

        return $this->renderView($params);
    }

    /**
     * @return Response|null
     */
    public function processSignForm()
    {
        $form = $this->getForm(DeclarationSign::class);
        $form->setData($this->getRequest()->getPost());
        if ($form->isValid()) {
            $data = (array) $this->getRequest()->getPost();
            if (isset($data['sign'])) {
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
                    'returnUrlOnError' => $this->url()->fromRoute(null, [], [], true),
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
            'licence/surrender/declaration/sign-with-external',
            [],
            [],
            true
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
        $form = $this->formHelper->createForm('GenericConfirmation');
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
        return [
            'title' => 'licence.surrender.declaration.title',
            'licNo' => $this->data['surrender']['licence']['licNo'],
            'content' => $this->translationHelper->translateReplace(
                'markup-licence-surrender-declaration',
                [$this->data['surrender']['licence']['licNo']]
            ),
            'form' => $this->form,
            'backLink' => $this->getLink('lva-licence'),
            'bottomLink' => $this->getBottomLinkRouteAndText()['bottomLinkRoute'],
            'bottomText' => $this->getBottomLinkRouteAndText()['bottomLinkText']
        ];
    }

    /**
     * @return null|string[]
     *
     * @psalm-return array{bottomLinkRoute: string, bottomLinkText: 'lva.section.title.transport-manager-application.print-sign'}|null
     */
    private function getBottomLinkRouteAndText(): ?array
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
