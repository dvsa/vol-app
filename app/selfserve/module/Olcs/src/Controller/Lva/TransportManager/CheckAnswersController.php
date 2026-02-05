<?php

namespace Olcs\Controller\Lva\TransportManager;

use Common\Controller\Lva\AbstractController;
use Common\Data\Mapper\Lva\TransportManagerApplication;
use Common\RefData;
use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Olcs\Controller\Lva\Traits\ExternalControllerTrait;
use Olcs\Controller\Lva\Traits\TransportManagerApplicationTrait;
use LmcRbacMvc\Service\AuthorizationService;

class CheckAnswersController extends AbstractController
{
    use ExternalControllerTrait;
    use TransportManagerApplicationTrait;

    /**
     * @param NiTextTranslation $niTextTranslationUtil
     * @param AuthorizationService $authService
     * @param FormHelperService $formHelper
     * @param TranslationHelperService $translationHelper
     * @param AnnotationBuilder $transferAnnotationBuilder
     * @param CommandService $commandService
     */
    public function __construct(
        NiTextTranslation $niTextTranslationUtil,
        AuthorizationService $authService,
        protected FormHelperService $formHelper,
        protected TranslationHelperService $translationHelper,
        protected AnnotationBuilder $transferAnnotationBuilder,
        protected CommandService $commandService
    ) {
        parent::__construct($niTextTranslationUtil, $authService);
    }

    #[\Override]
    public function indexAction()
    {

        $translator = $this->translationHelper;

        [$title, $defaultParams, $form] = $this->getPageLayout(
            $translator
        );

        $this->changeTmaStatusToDetailsSubmittedIfDetailsChecked();
        if (!empty($this->tma)) {
            $sections = TransportManagerApplication::mapForSections($this->tma, $translator);
            $sections = $this->addChangeSectionLink($sections, $this->tma);
        }
        $params = array_merge(["sections" => $sections], $defaultParams);
        /* @var $layout \Laminas\View\Model\ViewModel */
        $layout = $this->render($title, $form, $params);
        $content = $layout->getChildrenByCaptureTo('content')[0];
        $content->setTemplate('pages/lva-tm-details-checkAnswers');

        return $layout;
    }

    /**
     * confirmAction
     *
     * @return \Laminas\Http\Response
     */
    public function confirmAction()
    {
        $transportManagerApplicationId = $this->params("child_id");
        $this->updateTmaStatus(
            $transportManagerApplicationId,
            RefData::TMA_STATUS_DETAILS_CHECKED
        );
        return $this->redirectToTmDeclarationPage();
    }

    private function getConfirmationForm(): \Common\Form\Form
    {
        $formHelper = $this->formHelper;

        /* @var $form \Common\Form\Form */
        $form = $formHelper->createForm('GenericConfirmation');
        $form->setAttribute(
            "action",
            $this->url()->fromRoute(
                'lva-' . $this->lva . '/transport_manager_check_answer/action',
                [
                    'action' => 'confirm',
                    'application' => $this->tma['application']['id'],
                    'child_id' => $this->tma['id']
                ]
            )
        );
        $submitLabel = 'Confirm and continue';
        $form->setSubmitLabel($submitLabel);
        $form->removeCancel();
        return $form;
    }

    private function addChangeSectionLink(array $sections, array $transportManagerApplication): array
    {
        $lva = $transportManagerApplication['application']['isVariation'] ? 'variation' : 'application';
        foreach ($sections as $key => $value) {
            $sections[$key]['change']['sectionLink'] = $this->url()->fromRoute(
                'lva-' . $lva . '/transport_manager_details',
                [
                    'application' => $transportManagerApplication['application']['id'],
                    'child_id' => $transportManagerApplication['id'],
                ]
            ) . "#" . $sections[$key]['change']['sectionName'];
        }
        return $sections;
    }

    /**
     * getPageLayout
     *
     * @param object $translator
     * @param array  $transportManagerApplication
     * @param int    $transportManagerApplicationId
     *
     * @return array
     */
    private function getPageLayout($translator): array
    {
        $checkAnswersHint = $translator->translate('lva.section.transport-manager-check-answers-hint');
        $title = 'check_answers';
        $defaultParams = [
            'content' => $checkAnswersHint,
            'tmFullName' => $this->getTmName(),
            'backLink' => $this->url()->fromRoute(
                "dashboard",
                [],
                [],
                false
            ),
            'backText' => 'transport-manager-save-return',

        ];

        $form = $this->getConfirmationForm();
        return [$title, $defaultParams, $form];
    }

    private function redirectToTmDeclarationPage(): \Laminas\Http\Response
    {
        return $this->redirect()->toRoute(
            'lva-' . $this->lva . '/transport_manager_tm_declaration',
            [
                'child_id' => $this->params("child_id"),
                'application' => $this->params("application"),
                'action' => 'index'
            ]
        );
    }

    /**
     * @return void
     */
    private function changeTmaStatusToDetailsSubmittedIfDetailsChecked()
    {
        if (
            $this->tma['tmApplicationStatus']['id'] ===
            RefData::TMA_STATUS_DETAILS_CHECKED
        ) {
            $this->updateTmaStatus(
                $this->tma['id'],
                RefData::TMA_STATUS_DETAILS_SUBMITTED
            );
        }
    }

    /**
     * Is user permitted to access this controller
     *
     * @param array $transportManagerApplication
     *
     * @return bool
     */
    protected function isUserPermitted($transportManagerApplication)
    {
        if (
            $transportManagerApplication['isTmLoggedInUser'] &&
            ($transportManagerApplication['tmApplicationStatus']['id'] ===
                RefData::TMA_STATUS_DETAILS_SUBMITTED ||
                $transportManagerApplication['tmApplicationStatus']['id'] ===
                RefData::TMA_STATUS_DETAILS_CHECKED)
        ) {
            return true;
        }
        return false;
    }
}
