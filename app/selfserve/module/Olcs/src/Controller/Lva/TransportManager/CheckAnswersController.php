<?php

namespace OLCS\Controller\Lva\TransportManager;


use Common\Controller\Lva\AbstractTransportManagersController;
use Common\Data\Mapper\Lva\TransportManagerApplication;
use Olcs\Controller\Lva\Traits\ExternalControllerTrait;
use Dvsa\Olcs\Transfer\Command;

class CheckAnswersController extends AbstractTransportManagersController
{


    use ExternalControllerTrait;

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
     */
    public function confirmAction()
    {
        $flashMessenger = $this->getServiceLocator()->get('Helper\FlashMessenger');
        $transportManagerApplicationId = $this->params("child_id");
        $response = $this->handleCommand(
            Command\TransportManagerApplication\Submit::create(['id' => $transportManagerApplicationId])
        );

        if ($response->isOk()) {
            $flashMessenger->addSuccessMessage('lva-tm-details-submit-success');


            //redirect to declaration at this point.
            exit("Decalarion page -> OLCS-19791") . $transportManagerApplicationId;
        } else {
            $flashMessenger->addErrorMessage('unknown-error');
        }
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
                'lva-transport_manager/check_answers/action',
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
     * @param $translator
     * @param $transportManagerApplication
     * @param $transportManagerApplicationId
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

        $form = $this->getConfirmationForm($transportManagerApplicationId, $transportManagerApplication['application']['id']);
        return array($title, $defaultParams, $form);
    }
}
