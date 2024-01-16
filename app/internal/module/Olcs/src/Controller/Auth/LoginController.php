<?php
declare(strict_types=1);

namespace Olcs\Controller\Auth;

use Common\Auth\Service\AuthenticationServiceInterface;
use Common\Controller\Plugin\CurrentUser;
use Common\Controller\Plugin\Redirect;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Auth\Container\AuthChallengeContainer;
use Dvsa\Olcs\Auth\Service\Auth\CookieService;
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
use Olcs\Form\Model\Form\Auth\LoginForm;

class LoginController implements InjectApplicationEventInterface
{
    const FLASH_MESSAGE_NAMESPACE_INPUT = 'login-input';
    const FLASH_MESSAGE_NAMESPACE_AUTH_ERROR = 'auth-error';
    const TRANSLATION_KEY_SUFFIX_AUTH_INVALID_USERNAME_OR_PASSWORD = 'Authentication Failed';
    const TRANSLATION_KEY_SUFFIX_AUTH_ACCOUNT_DISABLED = 'account-disabled';

    const AUTH_SUCCESS_WITH_CHALLENGE = 2;
    const AUTH_FAILURE_ACCOUNT_DISABLED = -5;

    const ROUTE_AUTH_EXPIRED_PASSWORD = 'auth/expired-password';
    const ROUTE_AUTH_LOGIN_GET = 'auth/login/GET';
    const ROUTE_DASHBOARD = 'dashboard';
    const DVSA_OLCS_AUTH_CLIENT_OPENAM = 'Dvsa\Olcs\Auth\Client\OpenAm';
    const CHALLENGE_NEW_PASSWORD_REQUIRED = 'NEW_PASSWORD_REQUIRED';
    const DVSA_OLCS_AUTH_CLIENT_COGNITO = 'Dvsa\Olcs\Auth\Client\CognitoAdapter';

    /**
     * @var ValidatableAdapterInterface
     */
    private $authenticationAdapter;

    /** @var AuthenticationServiceInterface */
    protected $authenticationService;

    /**
     * @var CookieService
     */
    private $cookieService;

    /**
     * @var event;
     */
    private $event;

    /**
     * @var CurrentUser
     */
    private $currentUser;

    /**
     * @var FlashMessenger
     */
    private $flashMessenger;

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
     * @var Layout
     */
    private $layout;

    /**
     * @var AuthChallengeContainer
     */
    private AuthChallengeContainer $authChallengeContainer;

    /**
     * LoginController constructor.
     * @param ValidatableAdapterInterface $authenticationAdapter
     * @param AuthenticationServiceInterface $authenticationService
     * @param CookieService $cookieService
     * @param CurrentUser $currentUser
     * @param FlashMessenger $flashMessenger
     * @param FormHelperService $formHelper
     * @param Layout $layout
     * @param Redirect $redirectHelper
     * @param Url $urlHelper
     * @param AuthChallengeContainer $authChallengeContainer
     */
    public function __construct(
        ValidatableAdapterInterface $authenticationAdapter,
        AuthenticationServiceInterface $authenticationService,
        CookieService $cookieService,
        CurrentUser $currentUser,
        FlashMessenger $flashMessenger,
        FormHelperService $formHelper,
        Layout $layout,
        Redirect $redirectHelper,
        Url $urlHelper,
        AuthChallengeContainer $authChallengeContainer
    ) {
        $this->authenticationAdapter = $authenticationAdapter;
        $this->authenticationService = $authenticationService;
        $this->cookieService = $cookieService;
        $this->currentUser = $currentUser;
        $this->flashMessenger = $flashMessenger;
        $this->formHelper = $formHelper;
        $this->layout = $layout;
        $this->redirectHelper = $redirectHelper;
        $this->urlHelper = $urlHelper;
        $this->authChallengeContainer = $authChallengeContainer;
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
     * @param Request $request
     * @param RouteMatch $routeMatch
     * @param Response $response
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

        switch ($result->getCode() ?? 0) {
            case static::AUTH_FAILURE_ACCOUNT_DISABLED:
                $this->flashMessenger->addMessage(
                    static::TRANSLATION_KEY_SUFFIX_AUTH_ACCOUNT_DISABLED,
                    static::FLASH_MESSAGE_NAMESPACE_AUTH_ERROR
                );
                break;
            default:
                // VOL-2394: If the login fails for any other reason, use a generic invalid username or password error.
                $this->flashMessenger->addMessage(
                    static::TRANSLATION_KEY_SUFFIX_AUTH_INVALID_USERNAME_OR_PASSWORD,
                    static::FLASH_MESSAGE_NAMESPACE_AUTH_ERROR
                );
        }

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
     * @param array $data
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
     * @param Request $request
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
     * @param Result $result
     * @param Response $response
     * @param Request $request
     * @return Response
     */
    private function handleSuccessfulAuthentication(Result $result, Response $response, Request $request)
    {
        switch ($result->getIdentity()['provider']) {
            case static::DVSA_OLCS_AUTH_CLIENT_OPENAM:
                return $this->handleSuccessOpenAMResult($result->getIdentity(), $request, $response);
            case self::DVSA_OLCS_AUTH_CLIENT_COGNITO:
                return $this->handleSuccessCognitoResult();
            default:
                return $this->redirectHelper->toRoute(static::ROUTE_AUTH_LOGIN_GET);
        }
    }

    /**
     * @param array $identity
     * @param Request $request
     * @param Response $response
     * @return Response
     */
    private function handleSuccessOpenAMResult(array $identity, Request $request, Response $response)
    {
        $gotoUrl = $request->getQuery('goto', '');

        $this->cookieService->createTokenCookie($response, $identity['tokenId'], false);

        // The "goto" URL added by openAm is always http, if we are running https, then need to change it
        if ($request->getUri()->getScheme() === 'https') {
            $gotoUrl = str_replace('http://', 'https://', $gotoUrl);
        }

        if ($this->validateGotoUrl($gotoUrl, $request)) {
            return $this->redirectHelper->toUrl($gotoUrl);
        }

        return $this->redirectHelper->toRoute(static::ROUTE_DASHBOARD);
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
        return strpos($gotoUrl, $serverUrl) === 0;
    }

    /**
     * @param array $messages
     * @return Response
     */
    private function handleChallengeResult(array $messages): Response
    {
        switch ($messages['challengeName']) {
            case AuthChallengeContainer::CHALLENEGE_NEW_PASWORD_REQUIRED:
                $this->applyAuthChallengeContainer($messages);
                return $this->redirectHelper->toRoute(
                    self::ROUTE_AUTH_EXPIRED_PASSWORD,
                    $messages['challengeParameters'] // TODO: Remove passing this in once OpenAM removed
                );
            default:
                // Unsupported challenge so redirect to login page
                return $this->redirectHelper->toRoute(self::ROUTE_AUTH_LOGIN_GET);
        }
    }

    /**
     * @param array $messages
     */
    private function applyAuthChallengeContainer(array $messages): void
    {
        // OpenAM this key won't exist so we skip adding into session
        // TODO: Remove this check once OpenAM is removed
        if (!array_key_exists('USER_ID_FOR_SRP', $messages['challengeParameters'])) {
            return;
        }

        $this->authChallengeContainer
            ->setChallengeName($messages['challengeName'])
            ->setChallengeSession($messages['challengeSession'])
            ->setChallengedIdentity($messages['challengeParameters']['USER_ID_FOR_SRP']);
    }
}
