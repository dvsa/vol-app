<?php

namespace OLCS\Controller\Lva\TransportManager;

use Common\Controller\Lva\AbstractController;
use Common\Controller\Lva\Traits\TransportManagerApplicationTrait;
use Common\Data\Mapper\Lva\TransportManagerApplication;
use Olcs\Controller\Lva\Traits\ExternalControllerTrait;

class CheckAnswersController extends AbstractController
{
    use ExternalControllerTrait,
        TransportManagerApplicationTrait;

    public function indexAction()
    {
        $transportManagerApplicationId = $this->params("child_id");
        $transportManagerApplication = $this->getTransportManagerApplication($transportManagerApplicationId);
        $translator = $this->serviceLocator->get('Helper\Translation');

        list($title, $defaultParams, $form) = $this->getPageLayout(
            $translator,
            $transportManagerApplication,
            $transportManagerApplicationId
        );

        $sections = TransportManagerApplication::mapForSections($transportManagerApplication, $translator);
        $sections = $this->addChangeSectionLink($sections, $transportManagerApplication);
        $params = array_merge(["sections" => $sections], $defaultParams);
        /* @var $layout \Zend\View\Model\ViewModel */
        $layout = $this->render($title, $form, $params);
        $content = $layout->getChildrenByCaptureTo('content')[0];
        $content->setTemplate('pages/lva-tm-details-checkAnswers');

        return $layout;
    }

    /**
     * confirmAction
     *
     */
    public function confirmAction()
    {
        return $this->redirectToTmDeclarationPage();
    }

    /**
     * getConfirmationForm
     *
     * @param int $transportManagerApplicationId
     *
     * @param int $applicationId
     *
     * @return \Common\Form\Form
     */
    private function getConfirmationForm(int $transportManagerApplicationId, int $applicationId): \Common\Form\Form
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        /* @var $form \Common\Form\Form */
        $form = $formHelper->createForm('GenericConfirmation');
        $form->setAttribute(
            "action",
            $this->url()->fromRoute(
                'lva-' . $this->lva . '/transport_manager_check_answer/action',
                [
                    'action' => 'confirm',
                    'application' => $applicationId,
                    'child_id' => $transportManagerApplicationId
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
    private function getPageLayout($translator, $transportManagerApplication, $transportManagerApplicationId): array
    {
        $checkAnswersHint = $translator->translate('lva.section.transport-manager-check-answers-hint');
        $title = 'check_answers';
        $defaultParams = [
            'content' => $checkAnswersHint,
            'tmFullName' => $this->getTmName($transportManagerApplication),
            'backLink' => $this->url()->fromRoute(
                "dashboard",
                [],
                [],
                false
            ),
            'backText' => 'transport-manager-save-return',

        ];

        $form = $this->getConfirmationForm(
            $transportManagerApplicationId,
            $transportManagerApplication['application']['id']
        );
        return array($title, $defaultParams, $form);
    }

    private function redirectToTmDeclarationPage(): \Zend\Http\Response
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
}
