<?php

namespace OLCS\Controller\Lva\TransportManager;

use Common\Controller\Lva\AbstractTransportManagersController;
use \Common\Form\Form;
use Olcs\Controller\Lva\Traits\ExternalControllerTrait;

class DeclarationController extends AbstractTransportManagersController
{
    use ExternalControllerTrait;

    /**
     * index action for /transport-manager/[tmaId]/confirmation route
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $tmaId = (int)$this->params('child_id');
        $tma = $this->getTransportManagerApplication($tmaId);

        $translationHelper = $this->getServiceLocator()->get('Helper\Translation');
        $params = [
            'content' => $translationHelper->translate('markup-tma-declaration'),
            'tmFullName' => $this->getTmName($tma),
            'backLink' => $this->getBacklink($tma)
        ];

        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $form = $formHelper->createForm('TransportManagerApplicationDeclaration');
        /* @var $form \Common\Form\Form */
        $formHelper->setFormActionFromRequest($form, $this->getRequest());

        $this->alterDeclarationForm($form);

        $this->getServiceLocator()->get('Script')->loadFiles(['tm-lva-declaration']);

        $layout = $this->render('transport-manager-application.declaration', $form, $params);
        /* @var $layout \Zend\View\Model\ViewModel */

        $content = $layout->getChildrenByCaptureTo('content')[0];
        $content->setTemplate('pages/lva-tm-details-action');

        return $layout;
    }

    /**
     * Returns route: /[applicationOrVariation]/[applicationId]/transport-managers/details/[tmaId]
     *
     * @return string
     */
    private function getBacklink($tma)
    {
        return $this->url()->fromRoute(
            "lva-" . $this->returnApplicationOrVariation($tma) . "/transport_manager_details",
            ['child_id' => $this->getIdentifier(), 'application' => $tma["application"]["id"]],
            [],
            false
        );
    }

    private function alterDeclarationForm(Form $form)
    {
        $form->get('form-actions')->get('submit')->setLabel('application.review-declarations.sign-button');
    }

    /**
     * return "application" or "variation"
     *
     * @return string
     */
    private function returnApplicationOrVariation($tma)
    {
        if ($tma["application"]["isVariation"]) {
            return "variation";
        }
        return "application";
    }
}
