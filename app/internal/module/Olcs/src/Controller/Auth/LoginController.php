<?php

declare(strict_types=1);

namespace Olcs\Controller\Auth;

use Common\Auth\Service\AuthenticationServiceInterface;
use Common\Controller\Plugin\CurrentUser;
use Common\Controller\Plugin\Redirect;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Auth\Container\AuthChallengeContainer;
use Laminas\Authentication\Adapter\ValidatableAdapterInterface;
use Laminas\Authentication\Result;
use Laminas\EventManager\Event;
use Laminas\EventManager\EventInterface;
use Laminas\Form\Form;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\Mvc\Controller\Plugin\Layout;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\Mvc\InjectApplicationEventInterface;
use Laminas\Router\Http\RouteMatch;
use Laminas\View\Model\ViewModel;
use Olcs\Form\Model\Form\Auth\Login;

class LoginController implements InjectApplicationEventInterface
{
    public const FLASH_MESSAGE_NAMESPACE_INPUT = 'login-input';
    public const FLASH_MESSAGE_NAMESPACE_AUTH_ERROR = 'auth-error';
    public const TRANSLATION_KEY_SUFFIX_AUTH_INVALID_USERNAME_OR_PASSWORD = 'Authentication Failed';
    public const TRANSLATION_KEY_SUFFIX_AUTH_ACCOUNT_DISABLED = 'account-disabled';

    public const AUTH_SUCCESS_WITH_CHALLENGE = 2;
    public const AUTH_FAILURE_ACCOUNT_DISABLED = -5;

    public const ROUTE_AUTH_EXPIRED_PASSWORD = 'auth/expired-password';
    public const ROUTE_AUTH_LOGIN_GET = 'auth/login/GET';
    public const ROUTE_DASHBOARD = 'dashboard';
    public const CHALLENGE_NEW_PASSWORD_REQUIRED = 'NEW_PASSWORD_REQUIRED';
    public const DVSA_OLCS_AUTH_CLIENT_COGNITO = 'Dvsa\Olcs\Auth\Client\CognitoAdapter';

    /** @var AuthenticationServiceInterface */
    protected $authenticationService;

    /**
     * @var event;
     */
    private $event;

    /**
     * @var FormHelperService
     */
    protected $formHelper;

    /**
     * @var Redirect
     */
    protected $redirectHelper;

    /**
     * @var Url
     */
    protected $urlHelper;

    /**
     * LoginController constructor.
     */
    public function __construct(
        private ValidatableAdapterInterface $authenticationAdapter,
        AuthenticationServiceInterface $authenticationService,
        private CurrentUser $currentUser,
        private FlashMessenger $flashMessenger,
        FormHelperService $formHelper,
        private Layout $layout,
        Redirect $redirectHelper,
        Url $urlHelper,
        private AuthChallengeContainer $authChallengeContainer
    ) {
        $this->authenticationService = $authenticationService;
        $this->formHelper = $formHelper;
        $this->redirectHelper = $redirectHelper;
        $this->urlHelper = $urlHelper;
    }


    public function indexAction()
    {
        if (!$this->currentUser->getIdentity()->isAnonymous()) {
            return $this->redirectHelper->toRoute(static::ROUTE_DASHBOARD);
        }

        $this->layout->setTemplate('auth/layout');

        $view = new ViewModel();
        $view->setTemplate('pages/auth/login');

        $form = $this->createLoginForm($this->retrieveFormData());

        $view->setVariable('form', $form);

        if ($this->flashMessenger->hasMessages(static::FLASH_MESSAGE_NAMESPACE_AUTH_ERROR)) {
            $view->setVariables([
                'failed' => true,
                'failureReason' => $this->flashMessenger->getMessagesFromNamespace(static::FLASH_MESSAGE_NAMESPACE_AUTH_ERROR)[0]
            ]);
        }

        return $view;
    }

    /**
     * @return Response
     */
    public function postAction(Request $request, RouteMatch $routeMatch, Response $response): Response
    {
        if (!$this->currentUser->getIdentity()->isAnonymous()) {
            return $this->redirectHelper->toRoute(static::ROUTE_DASHBOARD);
        }

        $form = $this->createLoginForm($request->getPost()->toArray());

        if (!$form->isValid()) {
            $this->storeFormData($form);
            return $this->redirectHelper->toRoute(self::ROUTE_AUTH_LOGIN_GET);
        }

        $result = $this->attemptAuthentication($request);

        if ($result->getCode() === Result::SUCCESS) {
            return $this->handleSuccessfulAuthentication($result, $response, $request);
        }

        if ($result->getCode() === static::AUTH_SUCCESS_WITH_CHALLENGE) {
            return $this->handleChallengeResult($result->getMessages());
        }

        $this->storeFormData($form);

        match ($result->getCode() ?? 0) {
            static::AUTH_FAILURE_ACCOUNT_DISABLED => $this->flashMessenger->addMessage(
                static::TRANSLATION_KEY_SUFFIX_AUTH_ACCOUNT_DISABLED,
                static::FLASH_MESSAGE_NAMESPACE_AUTH_ERROR
            ),
            default => $this->flashMessenger->addMessage(
                static::TRANSLATION_KEY_SUFFIX_AUTH_INVALID_USERNAME_OR_PASSWORD,
                static::FLASH_MESSAGE_NAMESPACE_AUTH_ERROR
            ),
        };

        return $this->redirectHelper->toRoute(self::ROUTE_AUTH_LOGIN_GET);
    }

    /**
     * @param EventInterface $event
     */
    public function setEvent(EventInterface $event)
    {
        $this->event = $event;
    }

    /**
     * @return Event|EventInterface
     */
    public function getEvent()
    {
        if (!$this->event) {
            $this->setEvent(new Event());
        }

        return $this->event;
    }

    /**
     * @return Form
     */
    protected function createLoginForm(array $data = null): Form
    {
        $form = $this->formHelper->createForm(Login::class);
        if (!is_null($data)) {
            $form->setData($data);
            $form->isValid();
        }

        return $form;
    }

    /**
     * Store form data in session
     * @param $form
     */
    protected function storeFormData($form)
    {
        $this->flashMessenger->addMessage(json_encode($form->getData()), static::FLASH_MESSAGE_NAMESPACE_INPUT);
    }

    /**
     * Retrieve form data that has been flashed to session
     * @return array
     */
    protected function retrieveFormData(): ?array
    {
        if (!$this->flashMessenger->hasMessages(static::FLASH_MESSAGE_NAMESPACE_INPUT)) {
            return null;
        }

        return json_decode(array_values($this->flashMessenger->getMessages(static::FLASH_MESSAGE_NAMESPACE_INPUT))[0], true);
    }

    /**
     * @return Result
     */
    protected function attemptAuthentication(Request $request): Result
    {
        $username = $request->getPost('username');
        $password = $request->getPost('password');

        $this->authenticationAdapter->setIdentity($username);
        $this->authenticationAdapter->setCredential($password);

        $result = $this->authenticationService->authenticate($this->authenticationAdapter);
        return $result;
    }

    /**
     * Handles successful authentication.
     *
     * @return Response
     */
    private function handleSuccessfulAuthentication(Result $result, Response $response, Request $request)
    {
        $identityProvider = $result->getIdentity()['provider'];

        if ($identityProvider === self::DVSA_OLCS_AUTH_CLIENT_COGNITO) {
            return $this->handleSuccessCognitoResult();
        }

        return $this->redirectHelper->toRoute(static::ROUTE_AUTH_LOGIN_GET);
    }

    /**
     * @param array $identity
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    private function handleSuccessCognitoResult()
    {
        return $this->redirectHelper->toRoute(static::ROUTE_DASHBOARD);
    }

    /**
     * Validate that the goto URL is valid
     *
     * @param string $gotoUrl Goto URL
     *
     * @return bool
     */
    private function validateGotoUrl(string $gotoUrl, Request $request)
    {
        if (!is_string($gotoUrl) || empty($gotoUrl)) {
            return false;
        }

        // Check that the goto URL is valid, ie it begins with the server name from the request
        $uri = $request->getUri();
        $serverUrl = $uri->getScheme() . '://' . $uri->getHost() . '/';
        return str_starts_with($gotoUrl, $serverUrl);
    }

    /**
     * @return Response
     */
    private function handleChallengeResult(array $messages): Response
    {
        switch ($messages['challengeName']) {
            case AuthChallengeContainer::CHALLENEGE_NEW_PASWORD_REQUIRED:
                $this->applyAuthChallengeContainer($messages);
                return $this->redirectHelper->toRoute(self::ROUTE_AUTH_EXPIRED_PASSWORD);
            default:
                // Unsupported challenge so redirect to login page
                return $this->redirectHelper->toRoute(self::ROUTE_AUTH_LOGIN_GET);
        }
    }

    private function applyAuthChallengeContainer(array $messages): void
    {
        $this->authChallengeContainer
            ->setChallengeName($messages['challengeName'])
            ->setChallengeSession($messages['challengeSession'])
            ->setChallengedIdentity($messages['challengeParameters']['USER_ID_FOR_SRP']);
    }
}
