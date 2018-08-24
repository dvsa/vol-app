<?php

namespace OLCS\Controller\Lva\TransportManager;

use Common\Controller\Lva\AbstractTransportManagersController;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Transfer\Query\TransportManagerApplication\GetDetails;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;


class CheckAnswersController extends AbstractTransportManagersController
{

    use ApplicationControllerTrait;

    public function indexAction()
    {
        $transportManagerApplicationId = $this->params("application");

        $transportManagerApplication = $this->getTransportManagerApplication($transportManagerApplicationId);

        $title = 'check_answers';
        $defaultParams = [
            'tmFullName' => $this->getTmName($transportManagerApplication),
            'backLink' => $this->url()->fromRoute(
                "dashboard",
                [],
                [],
                false
            ),
            'backText' => 'transport-manager-save-return',

        ];

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


        $params = array_merge([/* data for sections */], $defaultParams);

        /* @var $layout \Zend\View\Model\ViewModel */
        $layout = $this->render($title, $form, $params);

        $content = $layout->getChildrenByCaptureTo('content')[0];

        $content->setTemplate('pages/lva-tm-details-checkAnswers');

        return $layout;
    }

    public function confirmAction()
    {
        if ($this->getRequest()->isPost()) {
            exit('declaration redirect');
        }
    }


}
