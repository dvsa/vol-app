<?php

namespace OLCS\Controller\Lva\TransportManager;

use Common\Controller\Lva\AbstractTransportManagersController;
use Common\Data\Mapper\Lva\TransportManagerApplication;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;


class CheckAnswersController extends AbstractTransportManagersController
{

    use ApplicationControllerTrait;

    public function indexAction()
    {
        $transportManagerApplicationId = $this->params("application");

        $transportManagerApplication = $this->getTransportManagerApplication($transportManagerApplicationId);
        $translator = $this->serviceLocator->get('Helper\Translation');

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

        $form = $this->getConfirmationForm($transportManagerApplicationId);
        $sections = TransportManagerApplication::mapForSections($transportManagerApplication);
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
        if ($this->getRequest()->isPost()) {
            exit("Decalarion page -> OLCS-19791");
        }
    }

    /**
     * getConfirmationForm
     *
     * @param $transportManagerApplicationId
     *
     * @return \Common\Form\Form
     */
    private function getConfirmationForm(int $transportManagerApplicationId): \Common\Form\Form
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        /* @var $form \Common\Form\Form */
        $form = $formHelper->createForm('GenericConfirmation');
        $form->setAttribute(
            "action",
            $this->url()->fromRoute(
                'lva-transport_manager/check_answers',
                ['application' => $transportManagerApplicationId]
            ) . 'confirm'
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
                'lva-' . $lva .'/transport_manager_details/change',
                [
                    'application' => $transportManagerApplication['application']['id'],
                    'child_id' => $transportManagerApplication['id'],
                    'activeSection' => $sections[$key]['change']['sectionName']
                ]
            );
        }
        return $sections;
    }
}
