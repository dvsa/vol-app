<?php

namespace Dvsa\Olcs\Auth\Controller;

use Common\Controller\Plugin\Redirect;
use Common\Service\Cqrs\Command\CommandSender;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Auth\Form\ChangePasswordForm;
use Dvsa\Olcs\Transfer\Command\Auth\ChangePassword;
use Dvsa\Olcs\Transfer\Result\Auth\ChangePasswordResult;
use Exception;
use Laminas\Form\Form;
use Laminas\View\Model\ViewModel;
use RuntimeException;

class ChangePasswordController extends AbstractController
{
    public const MESSAGE_BASE = "Expired Password Change Failed: %s";

    public const MESSAGE_RESULT_NOT_OK = 'Result is not ok';

    public function __construct(
        private FormHelperService $formHelperService,
        private FlashMessengerHelperService $flashMessenger,
        private array $config,
        private CommandSender $commandSender,
        protected Redirect $redirectPlugin
    ) {
        $this->redirectPlugin->setController($this);
    }

    /**
     * Forgot password page
     *
     * @return ViewModel|\Laminas\Http\Response
     *
     * @throws Exception
     *
     * @psalm-suppress ImplementedReturnTypeMismatch
     */
    #[\Override]
    public function indexAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        /** @var Form $form */
        $form = $this->formHelperService->createFormWithRequest(ChangePasswordForm::class, $request);

        if ($request->isPost() === false) {
            return $this->renderView($form);
        }

        if ($this->isButtonPressed('cancel')) {
            // redir to my account
            return $this->redirectToMyAccount();
        }

        $form->setData($request->getPost());

        if ($form->isValid() === false) {
            return $this->renderView($form);
        }

        /** @var array $data */
        $data = $form->getData();

        $result = $this->updatePasswordCommand($data['oldPassword'], $data['newPassword']);

        if ($result->isValid()) {
            $this->flashMessenger->addSuccessMessage('auth.change-password.success');

            // redir to my account
            return $this->redirectToMyAccount();
        }

        return $this->renderView($form, true, $result->getMessage());
    }

    /**
     * Redirect to My Account page
     *
     * @return \Laminas\Http\Response
     */
    private function redirectToMyAccount()
    {
        // redir to my account
        return $this->redirectPlugin->toRouteAjax($this->config['my_account_route']);
    }

    /**
     * @throws Exception
     */
    private function updatePasswordCommand(string $oldPassword, string $newPassword): ChangePasswordResult
    {
        $command = ChangePassword::create([
            'password' => $oldPassword,
            'newPassword' => $newPassword
        ]);

        $result = $this->commandSender->send($command);

        if (!$result->isOk()) {
            throw new RuntimeException(sprintf(static::MESSAGE_BASE, static::MESSAGE_RESULT_NOT_OK));
        }

        return ChangePasswordResult::fromArray($result->getResult()['flags'] ?? []);
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
        $view = new ViewModel(['form' => $form, 'failed' => $failed, 'failureReason' => $failureReason]);
        $view->setTemplate('auth/change-password');

        return $view;
    }
}
