<?php

namespace Olcs\Controller\Lva\TransportManager;

use Common\Form\Form;
use Common\RefData;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use LmcRbacMvc\Service\AuthorizationService;

class OperatorDeclarationController extends AbstractDeclarationController
{
    protected $declarationMarkup = 'markup-tma-operator_declaration';

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param TranslationHelperService $translationHelper
     * @param FormHelperService $formHelper
     * @param ScriptFactory $scriptFactory
     * @param AnnotationBuilder $transferAnnotatiobBuilder
     * @param CommandService $commandService
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        ScriptFactory $scriptFactory,
        AnnotationBuilder $transferAnnotatiobBuilder,
        CommandService $commandService
    ) {
        parent::__construct(
            $niTextTranslationUtil,
            $authService,
            $translationHelper,
            $formHelper,
            $scriptFactory,
            $transferAnnotatiobBuilder,
            $commandService
        );
    }

    protected function getSignAsRole(): string
    {
        return RefData::TMA_SIGN_AS_OP;
    }

    /**
     * Get the URL/link to go back
     *
     * @return string
     */
    protected function getBackLink(): string
    {
        return $this->url()->fromRoute(
            "lva-" . $this->returnApplicationOrVariation() . "/transport_manager_details",
            [
                'child_id' => $this->tma["id"],
                'application' => $this->tma["application"]["id"]
            ]
        );
    }

    /**
     * @return \Common\Service\Cqrs\Response
     */
    protected function handlePhysicalSignatureCommand(): \Common\Service\Cqrs\Response
    {
        $response = $this->handleCommand(
            Command\TransportManagerApplication\OperatorSigned::create(['id' => $this->tma['id']])
        );
        return $response;
    }

    #[\Override]
    protected function alterDeclarationForm(Form $form): void
    {
        if ($this->tma['tmDigitalSignature'] === null) {
            $form->remove('content');
        }

        parent::alterDeclarationForm($form);
    }

    /**
     * @return string
     */
    protected function getSubmitActionLabel(): string
    {
        return 'application.review-declarations.sign-button';
    }

    /**
     * Is user permitted to access this controller
     *
     * @return bool
     */
    protected function isUserPermitted()
    {
        if (
            !$this->tma['isTmLoggedInUser'] &&
            $this->tma['tmApplicationStatus']['id'] ===
            RefData::TMA_STATUS_OPERATOR_APPROVED
        ) {
            return true;
        }
        return false;
    }
}
