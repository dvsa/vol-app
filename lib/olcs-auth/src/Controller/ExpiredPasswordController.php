<?php

namespace Dvsa\Olcs\Auth\Controller;

use Common\Service\Cqrs\Command\CommandSender;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Auth\Container\AuthChallengeContainer;
use Dvsa\Olcs\Auth\Form\ChangePasswordForm;
use Dvsa\Olcs\Transfer\Command\Auth\ChangeExpiredPassword;
use Dvsa\Olcs\Transfer\Result\Auth\ChangeExpiredPasswordResult;
use Exception;
use Laminas\Authentication\Storage\Session;
use Laminas\Form\Form;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use RuntimeException;

/**
 * Expired Password Controller
 */
class ExpiredPasswordController extends AbstractActionController
{
    private const MESSAGE_BASE = "Expired Password Change Failed: %s";

    public const MESSAGE_RESULT_NOT_OK = 'Result is not ok';

    public const MESSAGE_CHALLENGE_NOT_NEW_PASSWORD_REQUIRED = 'Expected challenge name to be NEW_PASSWORD_REQUIRED';

    public const ROUTE_INDEX = 'index';

    public const ROUTE_LOGIN = 'auth/login/GET';

    protected Form $form;

    public function __construct(private AuthChallengeContainer $authChallengeContainer, protected CommandSender $commandSender, private FlashMessengerHelperService $flashMessenger, private FormHelperService $formHelper, private Session $sessionContainer)
    {
    }

    /**
     * Expired password page
     *
     * @return Response|ViewModel
     *
     * @psalm-suppress ImplementedReturnTypeMismatch
     */
    #[\Override]
    public function indexAction()
    {
        /** @var \Laminas\Http\Request $request */
        $request = $this->getRequest();

        $this->form = $this->formHelper->createForm(ChangePasswordForm::class);

        $this->form->remove('oldPassword');
        $this->form->getInputFilter()->remove('oldPassword');

        if ($request->isPost() === false) {
            return $this->renderView();
        }

        $this->form->setData($request->getPost());

        if ($this->form->isValid() === false) {
            return $this->renderView();
        }

        /** @var array $data */
        $data = $this->form->getData();

        return $this->updatePasswordCommand($data['newPassword']);
    }

    /**
     * Render the view
     *
     * @param bool $failed Failed
     * @param string|null $failureReason Failure reason
     */
    private function renderView(bool $failed = false, ?string $failureReason = null): ViewModel
    {
        $this->layout('auth/layout');
        $view = new ViewModel([
            'form' => $this->form,
            'failed' => $failed,
            'failureReason' => $failureReason
        ]);
        $view->setTemplate('auth/expired-password');

        return $view;
    }

    /**
     * @throws Exception
     *
     * @returns Response|ViewModel
     *
     * @return Response|ViewModel
     */
    private function updatePasswordCommand(string $newPassword)
    {
        if ($this->authChallengeContainer->getChallengeName() !== AuthChallengeContainer::CHALLENEGE_NEW_PASWORD_REQUIRED) {
            throw new RuntimeException(sprintf(self::MESSAGE_BASE, self::MESSAGE_CHALLENGE_NOT_NEW_PASSWORD_REQUIRED));
        }

        $command = ChangeExpiredPassword::create([
            'newPassword' => $newPassword,
            'challengeSession' => $this->authChallengeContainer->getChallengeSession(),
            'username' => $this->authChallengeContainer->getChallengedIdentity()
        ]);

        $result = $this->commandSender->send($command);

        // We OK?
        if (!$result->isOk()) {
            throw new RuntimeException(sprintf(self::MESSAGE_BASE, self::MESSAGE_RESULT_NOT_OK));
        }

        $result = ChangeExpiredPasswordResult::fromArray($result->getResult()['flags']);
        if (!$result->isValid() || $result->getIdentity() === []) {
            return $this->handleInvalidResponse($result);
        }

        $this->sessionContainer->write($result->getIdentity());
        $this->authChallengeContainer->clear();

        foreach ($result->getMessages() as $message) {
            $this->flashMessenger->addSuccessMessage($message);
        }

        return $this->redirect()->toRoute(static::ROUTE_INDEX);
    }

    /**
     * @return ViewModel|Response
     * @throws Exception
     */
    protected function handleInvalidResponse(ChangeExpiredPasswordResult $result)
    {
        if ($result->getCode() === ChangeExpiredPasswordResult::FAILURE_NEW_PASSWORD_INVALID) {
            $element = $this->form->get('newPassword');
            $element->setMessages(['auth.expired-password.failed.reason.New password does not meet the password policy requirements.']);
            return $this->renderView(true, 'auth.expired-password.failed.reason.New password does not meet the password policy requirements.');
        }

        if ($result->getCode() === ChangeExpiredPasswordResult::FAILURE_NOT_AUTHORIZED) {
            foreach ($result->getMessages() as $message) {
                $this->flashMessenger->addErrorMessage($message);
            }

            return $this->redirect()->toRoute(static::ROUTE_LOGIN);
        }

        if ($result->getCode() === ChangeExpiredPasswordResult::FAILURE_NEW_PASSWORD_MATCHES_OLD) {
            $element = $this->form->get('newPassword');
            $element->setMessages(['auth.expired-password.failed.reason.The password must be different. Try again.']);
            return $this->renderView(true, 'auth.expired-password.failed.reason.The password must be different. Try again.');
        }

        throw new RuntimeException(sprintf("Invalid response from ChangeExpiredPassword Command: %s", implode('. ', $result->getMessages())));
    }
}
