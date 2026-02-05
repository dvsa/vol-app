<?php

namespace Olcs\Controller\Lva\TransportManager;

use Common\Data\Mapper\Lva\TransportManagerApplication;
use Common\RefData;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Command;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use LmcRbacMvc\Service\AuthorizationService;

class TmDeclarationController extends AbstractDeclarationController
{
    protected $declarationMarkup = 'markup-tma-tm_declaration';

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
        return $this->tma['isOwner'] === "N" ? RefData::TMA_SIGN_AS_TM : RefData::TMA_SIGN_AS_TM_OP;
    }

    /**
     * Get the URL/link to go back
     *
     * @return string
     */
    protected function getBackLink(): string
    {
        return $this->url()->fromRoute(
            'lva-' . $this->lva . '/transport_manager_check_answer',
            [
                'action' => 'index',
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
            Command\TransportManagerApplication\Submit::create(['id' => $this->tma['id']])
        );
        return $response;
    }

    /**
     * @return string
     */
    protected function getSubmitActionLabel(): string
    {
        $submitText = $this->tma['isOwner'] === "Y" ? 'submit' : 'submit-for-operator-approval';

        $label = $this->tma['disableSignatures'] === false
            ? 'application.review-declarations.sign-button'
            : $submitText;
        return $label;
    }

    /**
     * Is user permitted to access this controller
     *
     * @return bool
     */
    protected function isUserPermitted()
    {
        if (
            $this->tma['isTmLoggedInUser'] &&
            $this->tma['tmApplicationStatus']['id'] === RefData::TMA_STATUS_DETAILS_CHECKED
        ) {
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    protected function getTranslatedDeclarationMarkupParams(TranslationHelperService $translationHelper)
    {
        $translated = $translationHelper->translate(
            'tma-tm_declaration.residency-clause.' . $this->tma['application']['goodsOrPsv']['id']
        );

        return [$translated, $translated];
    }
}
