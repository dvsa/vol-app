<?php

namespace Olcs\Controller\Lva\TransportManager;

use Common\Controller\Lva\AbstractController;
use Common\Data\Mapper\Lva\TransportManagerApplication;
use Common\FeatureToggle;
use Common\Form\Form;
use Common\RefData;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Command\GovUkAccount\GetGovUkAccountRedirect;
use Dvsa\Olcs\Transfer\Query\FeatureToggle\IsEnabled as IsEnabledQry;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\View\Model\ViewModel as LaminasViewModel;
use Olcs\Controller\Lva\Traits\ExternalControllerTrait;
use Olcs\Controller\Lva\Traits\TransportManagerApplicationTrait;
use LmcRbacMvc\Service\AuthorizationService;

abstract class AbstractDeclarationController extends AbstractController
{
    use ExternalControllerTrait;
    use TransportManagerApplicationTrait;

    protected $declarationMarkup;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param TranslationHelperService $translationHelper
     * @param FormHelperService $formHelper
     * @param ScriptFactory $scriptFactory
     * @param AnnotationBuilder $transferAnnotationBuilder
     * @param CommandService $commandService
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        protected TranslationHelperService $translationHelper,
        protected FormHelperService $formHelper,
        protected ScriptFactory $scriptFactory,
        protected AnnotationBuilder $transferAnnotationBuilder,
        protected CommandService $commandService
    ) {
        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * Index action for the lva-transport_manager/tm_declaration and lva-transport_manager/declaration routes
     *
     * @return \Laminas\View\Model\ViewModel
     */
    #[\Override]
    public function indexAction(): LaminasViewModel
    {
        if ($this->getRequest()->isPost()) {
            if ($this->params()->fromPost('content')['isDigitallySigned'] === 'Y') {
                $this->digitalSignatureAction();
            } else {
                $this->physicalSignatureAction();
            }
        }
        return $this->renderDeclarationPage();
    }

    /**
     * @param array $tma
     *
     * @return LaminasViewModel
     */
    private function renderDeclarationPage(): LaminasViewModel
    {
        $translationHelper = $this->translationHelper;

        $params = [
            'content' => $translationHelper->translateReplace(
                $this->declarationMarkup,
                $this->getTranslatedDeclarationMarkupParams($translationHelper)
            ),
            'tmFullName' => $this->getTmName(),
            'backText' => 'common.link.back.label',
            'backLink' => $this->getBackLink()
        ];

        $formHelper = $this->formHelper;
        $form = $formHelper->createForm('TransportManagerApplicationDeclaration');
        /* @var $form \Common\Form\Form */
        $formHelper->setFormActionFromRequest($form, $this->getRequest());

        $this->alterDeclarationForm($form);

        $this->scriptFactory->loadFiles(['tm-lva-declaration']);

        $hasGovUkAccountError = $this->getFlashMessenger()->getContainer()->offsetExists('govUkAccountError');
        if ($hasGovUkAccountError) {
            $form->setMessages([
                'content' => [
                    'isDigitallySigned' => ['undertakings-sign-declaration-again']
                ],
            ]);
            $form->setOption('formErrorsParagraph', 'undertakings-govuk-account-generic-error');
        }

        $layout = $this->render('transport-manager-application.declaration', $form, $params);
        /* @var $layout \Laminas\View\Model\ViewModel */

        $content = $layout->getChildrenByCaptureTo('content')[0];
        $content->setTemplate('pages/lva-tm-details-action');

        return $layout;
    }

    /**
     * Return an array of parameters to be used as included as translateReplace inputs to the declaration markup
     *
     *
     * @return array
     */
    protected function getTranslatedDeclarationMarkupParams(TranslationHelperService $translator)
    {
        return [];
    }

    protected function digitalSignatureAction(): \Laminas\Http\Response
    {
        $role = $this->getSignAsRole();

        $returnUrl = $this->url()->fromRoute(
            'lva-' . $this->lva . '/transport_manager_confirmation',
            [
                'child_id' => $this->tma['id'],
                'application' => $this->tma['application']['id'],
                'action' => 'index'
            ],
            [],
            true
        );

        $urlResult = $this->handleCommand(GetGovUkAccountRedirect::create([
            'journey' => RefData::JOURNEY_TM_APPLICATION,
            'id' => $this->tma['id'],
            'role' => $role,
            'returnUrl' => $returnUrl,
            'returnUrlOnError' => $this->url()->fromRoute(null, [], [], true),
        ]));
        if (!$urlResult->isOk()) {
            throw new \Exception('GetGovUkAccountRedirect command returned non-OK', $urlResult->getStatusCode());
        }
        return $this->redirect()->toUrl($urlResult->getResult()['messages'][0]);
    }

    /**
     * Action for when the operator chooses to physically sign the transport manager application
     *
     * @param array $tma
     *
     * @return \Laminas\Http\Response
     */
    private function physicalSignatureAction()
    {
        $response = $this->handlePhysicalSignatureCommand();

        if ($response->isOk()) {
            return $this->redirect()->toRoute(
                "lva-" . $this->returnApplicationOrVariation() . "/transport_manager_details",
                [
                    'child_id' => $this->tma["id"],
                    'application' => $this->tma["application"]["id"]
                ]
            );
        } else {
            $this->flashMessenger()->addErrorMessage('unknown-error');
            return $this->redirect()->refresh();
        }
    }

    abstract protected function handlePhysicalSignatureCommand(): \Common\Service\Cqrs\Response;

    abstract protected function getSubmitActionLabel(): string;

    abstract protected function getBackLink(): string;

    abstract protected function getSignAsRole(): string;

    /**
     * Alter declaration form
     *
     *
     * @return void
     */
    protected function alterDeclarationForm(Form $form): void
    {
        $label = $this->getSubmitActionLabel();

        $form->get('form-actions')->get('submit')->setLabel($label);

        if ($this->tma['disableSignatures']) {
            $form->remove('content');
        }
    }
}
