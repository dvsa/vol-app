<?php

namespace Olcs\Controller\Lva;

use Common\Controller\Lva\AbstractController;
use Olcs\Controller\Interfaces\ApplicationControllerInterface;
use Zend\View\Model\ViewModel;

/**
 * Abstract Internal Application Decsision Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
abstract class AbstractApplicationDecisionController extends AbstractController implements
    ApplicationControllerInterface
{
    protected $cancelMessageKey;
    protected $successMessageKey;
    protected $titleKey;

    public function indexAction()
    {
        $helperFlashMsgr = $this->getServiceLocator()->get('Helper\FlashMessenger');

        /** @var \Zend\Http\Request $request */
        $request = $this->getRequest();
        $id      = $this->params('application');

        /** @var \Zend\Form\FormInterface $form */
        $form    = $this->getForm();

        if ($request->isPost()) {
            if ($this->isButtonPressed('cancel')) {
                $helperFlashMsgr->addWarningMessage($this->cancelMessageKey);
                return $this->redirect()->toRouteAjax('lva-' . $this->lva, ['application' => $id]);
            }

            $postData = (array)$request->getPost();
            $form->setData($postData);

            if ($form->isValid()) {
                $data = $form->getData();

                $response = $this->processDecision($id, $data);

                if ($response->isOk()) {
                    $message = $this->getServiceLocator()->get('Helper\Translation')
                        ->translateReplace($this->successMessageKey, [$id]);

                    $helperFlashMsgr->addSuccessMessage($message);
                } else {
                    $helperFlashMsgr->addErrorMessage('unknown-error');
                }

                return $this->redirectToApp($id);
            }
        }

        $view = new ViewModel(['title' => $this->titleKey, 'form' => $form]);
        $view->setTemplate('sections/lva/lva-details');

        return $this->render($view);
    }

    /**
     * @return \Common\Service\Cqrs\Response
     */
    abstract protected function processDecision($id, $data);

    abstract protected function getForm();

    protected function redirectToApp($applicationId)
    {
        return $this->redirect()->toRouteAjax(
            'lva-application/overview',
            ['application' => $applicationId]
        );
    }

    /**
     * Check for redirect
     *
     * @param int $lvaId
     * @return null
     */
    protected function checkForRedirect($lvaId)
    {
        // no-op to avoid LVA predispatch magic kicking in
    }
}
