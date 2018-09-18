<?php

namespace OLCS\Controller\Lva\TransportManager;

use Common\Controller\Lva\AbstractTransportManagersController;
use \Common\Form\Form;
use Olcs\Controller\Lva\Traits\ExternalControllerTrait;
use \Zend\View\Model\ViewModel as ZendViewModel;
use Dvsa\Olcs\Transfer\Command;

class DeclarationController extends AbstractTransportManagersController
{
    use ExternalControllerTrait;

    /**
     * Index action for the lva-transport_manager/declaration route
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction(): ZendViewModel
    {
        $tmaId = (int)$this->params('child_id');
        $tma = $this->getTransportManagerApplication($tmaId);

        if ($this->getRequest()->isPost()) {
            if ($_POST["content"]["isDigitallySigned"] === 'Y') {
                $this->digitalSignatureAction();
            } else {
                $this->physicalSignatureAction($tma);
            }
        }
        return $this->renderDeclarationPage($tma);
    }

    /**
     * @param array $tma
     *
     * @return ZendViewModel
     */
    private function renderDeclarationPage($tma): ZendViewModel
    {
        $translationHelper = $this->getServiceLocator()->get('Helper\Translation');
        $params = [
            'content' => $translationHelper->translate('markup-tma-declaration'),
            'tmFullName' => $this->getTmName($tma),
            'backLink' => $this->getBackLink($tma)
        ];

        $formHelper = $this->getServiceLocator()->get('Helper\Form');
        $form = $formHelper->createForm('TransportManagerApplicationDeclaration');
        /* @var $form \Common\Form\Form */
        $formHelper->setFormActionFromRequest($form, $this->getRequest());

        $this->alterDeclarationForm($form);

        if ($tma['disableSignatures']) {
            $formHelper->remove($form, 'content');
        }

        $this->getServiceLocator()->get('Script')->loadFiles(['tm-lva-declaration']);

        $layout = $this->render('transport-manager-application.declaration', $form, $params);
        /* @var $layout \Zend\View\Model\ViewModel */

        $content = $layout->getChildrenByCaptureTo('content')[0];
        $content->setTemplate('pages/lva-tm-details-action');

        return $layout;
    }

    private function digitalSignatureAction()
    {
        exit("external gov verify journey starts from here");
    }

    /**
     * Action for when the operator chooses to physically sign the transport manager application
     *
     * @param array $tma
     *
     * @return \Zend\Http\Response
     */
    private function physicalSignatureAction($tma)
    {
        // approve Operator
        $response = $this->handleCommand(
            Command\TransportManagerApplication\OperatorApprove::create(['id' => $tma['id']])
        );

        $flashMessenger = $this->getServiceLocator()->get('Helper\FlashMessenger');
        if ($response->isOk()) {
            // does the flash work????
            $flashMessenger->addSuccessMessage('operator-approve-message');
            return $this->redirect()->toUrl($this->getTransportManagerDetailsUrl($tma));
        } else {
            $flashMessenger->addErrorMessage('unknown-error');
        }
    }

    /**
     * Returns url: /[applicationOrVariation]/[applicationId]/transport-managers/details/[tmaId]
     *
     * @param array $tma
     *
     * @return string
     */
    private function getTransportManagerDetailsUrl($tma): string
    {
        return $this->url()->fromRoute(
            "lva-" . $this->returnApplicationOrVariation($tma) . "/transport_manager_details",
            ['child_id' => $tma["id"], 'application' => $tma["application"]["id"]],
            [],
            false
        );
    }

    /**
     * Get the URL/link to go back
     *
     * @param array $tma
     *
     * @return string
     */
    private function getBackLink($tma): string
    {
        return $this->url()->fromRoute(
            "lva-" . $this->returnApplicationOrVariation($tma) . "/transport_managers",
            ['application' => $tma["application"]["id"]],
            [],
            false
        );
    }

    /**
     * Alter declaration form
     *
     * @param Form $form
     *
     * @return void
     */
    private function alterDeclarationForm(Form $form): void
    {
        $form->get('form-actions')->get('submit')->setLabel('application.review-declarations.sign-button');
    }

    /**
     * Returns "application" or "variation"
     *
     * @param array $tma
     *
     * @return string
     */
    private function returnApplicationOrVariation($tma): string
    {
        if ($tma["application"]["isVariation"]) {
            return "variation";
        }
        return "application";
    }
}
