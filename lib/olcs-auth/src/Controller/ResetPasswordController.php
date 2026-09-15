<?php

namespace Dvsa\Olcs\Auth\Controller;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Laminas\Form\Form;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Dvsa\Olcs\Auth\Form\ResetPasswordForm;
use Dvsa\Olcs\Auth\Service\Auth\PasswordService;

class ResetPasswordController extends AbstractActionController
{
    public function __construct(private FormHelperService $formHelperService, private FlashMessengerHelperService $flashMessenger, private PasswordService $passwordService)
    {
    }

    /**
     * Reset password
     *
     * @return \Laminas\Http\Response|ViewModel
     *
     * @psalm-suppress ImplementedReturnTypeMismatch
     */
    #[\Override]
    public function indexAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        /** @var Form $form */
        $form = $this->formHelperService->createFormWithRequest(ResetPasswordForm::class, $request);

        if ($request->isPost() === false) {
            return $this->renderView($form);
        }

        $form->setData($request->getPost());

        if ($form->isValid() === false) {
            return $this->renderView($form);
        }

        /** @var array $data */
        $data = $form->getData();

        $confirmationId = $this->params()->fromQuery('confirmationId');
        $tokenId = $this->params()->fromQuery('tokenId');
        $username = $this->params()->fromQuery('username');

        $result = $this->passwordService->resetPassword($username, $confirmationId, $tokenId, $data['newPassword']);

        if ($result['flags']['success']) {
            $this->flashMessenger->addSuccessMessage('auth.reset-password.success');
            return $this->redirect()->toRoute('auth/login/GET');
        }

        return $this->renderView($form, true, $result['messages'][0] ?? '');
    }

    /**
     * Render the view
     *
     * @param \Laminas\Form\Form $form          Form
     * @param bool            $failed        Failed
     * @param string          $failureReason Failure reason
     *
     * @return ViewModel
     */
    private function renderView(\Laminas\Form\Form $form, $failed = false, $failureReason = null)
    {
        $this->layout('auth/layout');
        $view = new ViewModel(['form' => $form, 'failed' => $failed, 'failureReason' => $failureReason]);
        $view->setTemplate('auth/reset-password');

        return $view;
    }
}
