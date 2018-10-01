<?php

namespace OLCS\Controller\Lva\TransportManager;

use \Common\Form\Form;
use Olcs\Controller\Lva\Traits\ExternalControllerTrait;
use Common\Controller\Lva\AbstractController;
use Olcs\Controller\Lva\Traits\TransportManagerApplicationTrait;
use \Zend\View\Model\ViewModel as ZendViewModel;

abstract class AbstractDeclarationController extends AbstractController
{
    use ExternalControllerTrait,
        TransportManagerApplicationTrait;

    const SIGN_AS_TM = 'tm';
    const SIGN_AS_OP = 'op';
    const SIGN_AS_TM_OP = 'tm_op';

    protected $declarationMarkup;

    protected $tma;

    /**
     * Index action for the lva-transport_manager/tm_declaration and lva-transport_manager/declaration routes
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction(): ZendViewModel
    {
        if ($this->getRequest()->isPost()) {
            if ($this->params()->fromPost('content')['isDigitallySigned'] === 'Y') {
                $this->digitalSignatureAction();
            } else {
                $this->physicalSignatureAction();
            }
        }
        return $this->renderDeclarationPage();
    }

    /**
     * @param array $tma
     *
     * @return ZendViewModel
     */
    private function renderDeclarationPage(): ZendViewModel
    {
        $translationHelper = $this->getServiceLocator()->get('Helper\Translation');

        $content = $translationHelper->translate($this->declarationMarkup);
        $params = [
            'content' => $content,
            'tmFullName' => $this->getTmName(),
            'backLink' => $this->getBackLink()
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

    protected function digitalSignatureAction()
    {
        // write method body
    }

    /**
     * Action for when the operator chooses to physically sign the transport manager application
     *
     * @param array $tma
     *
     * @return \Zend\Http\Response
     */
    private function physicalSignatureAction()
    {
        $response = $this->handlePhysicalSignatureCommand();

        if ($response->isOk()) {
            return $this->redirect()->toRoute(
                "lva-" . $this->returnApplicationOrVariation() . "/transport_manager_details",
                [
                    'child_id' => $this->tma["id"],
                    'application' => $this->tma["application"]["id"]
                ]
            );
        } else {
            $this->flashMessenger()->addErrorMessage('unknown-error');
        }
    }

    abstract protected function handlePhysicalSignatureCommand(): \Common\Service\Cqrs\Response;

    abstract protected function getSubmitActionLabel(): string;

    abstract protected function getBackLink(): string;

    abstract protected function getSignAsRole(): string;

    /**
     * Alter declaration form
     *
     * @param Form $form
     *
     * @return void
     */
    protected function alterDeclarationForm(Form $form): void
    {
        $label = $this->getSubmitActionLabel();

        $form->get('form-actions')->get('submit')->setLabel($label);

        if ($this->tma['disableSignatures']) {
            $form->remove('content');
        }
    }
}
