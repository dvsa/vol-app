<?php

namespace Olcs\Controller\Lva\TransportManager;

use Common\Controller\Lva\AbstractController;
use Common\RefData;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Lva\Traits\ExternalControllerTrait;
use Olcs\Controller\Lva\Traits\TransportManagerApplicationTrait;
use LmcRbacMvc\Service\AuthorizationService;

class ConfirmationController extends AbstractController
{
    use ExternalControllerTrait;
    use TransportManagerApplicationTrait;

    public const TM_MARKUP = 'markup-tma-confirmation-tm';

    public const OPERATOR_MARKUP = 'markup-tma-confirmation-operator';

    protected $markup = self::OPERATOR_MARKUP;

    protected $signature;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param TranslationHelperService $translationHelper
     * @param AnnotationBuilder $transferAnnotationBuilder
     * @param CommandService $commandService
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        protected TranslationHelperService $translationHelper,
        protected AnnotationBuilder $transferAnnotationBuilder,
        protected CommandService $commandService
    ) {
        parent::__construct($niTextTranslationUtil, $authService);
    }

    /**
     * index action for /transport-manager/:TmaId/confirmation route
     *
     * @return \Laminas\View\Model\ViewModel
     */
    #[\Override]
    public function indexAction()
    {
        $translationHelper = $this->translationHelper;

        $this->signature = $this->tma['opDigitalSignature'];

        $this->setMarkupAndSignatureIfTm();

        $params = [
            'content' => $translationHelper->translateReplace(
                $this->markup,
                [
                    $this->getSignatureFullName($this->signature),
                    $this->getSignatureDate($this->signature),
                    $this->getBacklink()
                ]
            ),
            'tmFullName' => $this->getTmName(),
        ];

        return $this->renderTmAction(null, null, $params);
    }

    /**
     * Render the Transport manager application confirmation pages
     *
     * @param string            $title  Title
     * @param \Common\Form\Form $form   Form
     * @param array             $params Params
     *
     * @return \Laminas\View\Model\ViewModel
     */
    private function renderTmAction($title, $form, $params)
    {
        $defaultParams = [
            'tmFullName' => $this->getTmName(),
            'backLink' => $this->getBacklink(),
        ];

        $params = array_merge($defaultParams, $params);

        $layout = $this->render($title, $form, $params);
        /* @var $layout \Laminas\View\Model\ViewModel */

        $content = $layout->getChildrenByCaptureTo('content')[0];
        $content->setTemplate('pages/confirmation');

        return $layout;
    }

    /**
     * Get the URL/link to go back
     *
     * @return string
     */
    private function getBacklink()
    {
        if ($this->isOperatorUserOrAdmin()) {
            return $this->url()->fromRoute(
                "lva-{$this->lva}/transport_managers",
                ['application' => $this->getIdentifier()],
                [],
                false
            );
        }
        // in this context, if not an operator the user is a TM
        return $this->url()->fromRoute('dashboard');
    }

    private function isOperatorUserOrAdmin(): bool
    {
        if ($this->isGranted(RefData::PERMISSION_SELFSERVE_LVA)) {
            return true;
        }
        return false;
    }

    private function getSignatureDate($signature): string
    {
        $unixTimeStamp = strtotime((string) $signature['createdOn']);
        return date("j M Y", $unixTimeStamp);
    }

    private function getSignatureFullName($signature): string
    {
        $attributes = json_decode((string) $signature['attributes']);
        return $attributes->firstname . ' ' . $attributes->surname;
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
            ($this->tma['tmApplicationStatus']['id'] === RefData::TMA_STATUS_TM_SIGNED ||
                $this->tma['tmApplicationStatus']['id'] === RefData::TMA_STATUS_RECEIVED) &&
            !is_null($this->tma['tmDigitalSignature'])
        ) {
            return true;
        }

        if (
            (!$this->tma['isTmLoggedInUser']) &&
            $this->tma['tmApplicationStatus']['id'] === RefData::TMA_STATUS_RECEIVED &&
            !is_null($this->tma['opDigitalSignature'])
        ) {
            return true;
        }
        return false;
    }

    private function setMarkupAndSignatureIfTm(): void
    {
        if ($this->tma['isTmLoggedInUser'] && $this->tma["isOwner"] === "N") {
            $this->markup = self::TM_MARKUP;
            $this->signature = $this->tma['tmDigitalSignature'];
        }
    }
}
