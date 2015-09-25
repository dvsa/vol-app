<?php

/**
 * Abstract Internal Application Decsision Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
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
    protected $lva;
    protected $location;

    protected $cancelMessageKey;
    protected $successMessageKey;
    protected $titleKey;

    public function indexAction()
    {
        $request = $this->getRequest();
        $id      = $this->params('application');
        $form    = $this->getForm();

        if ($request->isPost()) {

            if ($this->isButtonPressed('cancel')) {
                $this->getServiceLocator()->get('Helper\FlashMessenger')
                    ->addWarningMessage($this->cancelMessageKey);
                return $this->redirect()->toRouteAjax('lva-'.$this->lva, ['application' => $id]);
            }

            $postData = (array)$request->getPost();
            $form->setData($postData);

            if ($form->isValid()) {

                $data = $form->getData();
                $this->processDecision($id, $data);

                $message = $this->getServiceLocator()->get('Helper\Translation')
                    ->translateReplace($this->successMessageKey, [$id]);

                $this->getServiceLocator()->get('Helper\FlashMessenger')
                    ->addSuccessMessage($message);

                return $this->redirectOnSuccess($id);
            }
        }

        $view = new ViewModel(['title' => $this->titleKey, 'form' => $form]);
        $view->setTemplate('sections/lva/lva-details');

        return $this->render($view);
    }

    abstract protected function processDecision($id, $data);

    abstract protected function getForm();

    /**
     * Default behaviour on success is to redirect to Licence overview page
     */
    protected function redirectOnSuccess($applicationId)
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
