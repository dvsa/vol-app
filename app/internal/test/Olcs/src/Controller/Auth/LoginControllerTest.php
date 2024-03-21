<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Auth;

use Common\Auth\Service\AuthenticationServiceInterface;
use Common\Controller\Plugin\CurrentUser;
use Common\Controller\Plugin\Redirect;
use Common\Rbac\User;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Auth\Container\AuthChallengeContainer;
use Dvsa\Olcs\Auth\Service\Auth\CookieService;
use Laminas\Authentication\Result;
use Laminas\Form\Form;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Http\Response as HttpResponse;
use Laminas\Mvc\Plugin\FlashMessenger\FlashMessenger;
use Laminas\Mvc\Controller\Plugin\Layout;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\Router\Http\RouteMatch;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\Parameters;
use Laminas\View\Model\ViewModel;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Auth\Adapter\InternalCommandAdapter;
use Olcs\Controller\Auth\LoginController;

class LoginControllerTest extends MockeryTestCase
{
    public const EMPTY_FORM_DATA = [
        'username' => null,
        'password' => null,
        'csrf' => null,
    ];

    public const AUTHENTICATION_RESULT_CHALLENGE_NEW_PASSWORD_REQUIRED = [
        LoginController::AUTH_SUCCESS_WITH_CHALLENGE,
        [],
        [
            'challengeName' => LoginController::CHALLENGE_NEW_PASSWORD_REQUIRED,
            'challengeParameters' => [
                'USER_ID_FOR_SRP' => 'username'
            ],
            'challengeSession' => 'challengeSession'
        ]
    ];
    public const AUTHENTICATION_RESULT_CHALLENGE_UNSUPPORTED = [
        LoginController::AUTH_SUCCESS_WITH_CHALLENGE,
        [],
        [
            'challengeName' => 'UnsupportedChallenge',
        ]
    ];
    public const AUTHENTICATION_RESULT_FAILURE = [Result::FAILURE, [], ['failed']];
    public const AUTHENTICATION_RESULT_USER_NOT_EXIST = [Result::FAILURE_IDENTITY_NOT_FOUND, [], ['Authentication Failed']];
    public const AUTHENTICATION_RESULT_CREDENTIAL_INVALID = [Result::FAILURE_CREDENTIAL_INVALID, [], ['Authentication Failed']];
    public const AUTHENTICATION_RESULT_FAILURE_ACCOUNT_DISABLED = [LoginController::AUTH_FAILURE_ACCOUNT_DISABLED, [], ['account-disabled']];
    public const AUTHENTICATION_RESULT_SUCCESSFUL_COGNITO = [Result::SUCCESS, ['provider' => LoginController::DVSA_OLCS_AUTH_CLIENT_COGNITO]];
    public const AUTHENTICATION_RESULT_SUCCESSFUL_UNKNOW_PROVIDER = [Result::SUCCESS, [ 'provider' => 'unknown']];

    /**
     * @var LoginController
     */
    protected $sut;

    public function setUp(): void
    {
        $this->authenticationAdapter = $this->createMock(InternalCommandAdapter::class);
        $this->authenticationService = $this->createMock(AuthenticationServiceInterface::class);
        $this->currentUser = $this->createMock(CurrentUser::class);
        $this->flashMessenger = $this->createMock(FlashMessenger::class);
        $this->formHelper = $this->createMock(FormHelperService::class);
        $this->layout = $this->createMock(Layout::class);
        $this->redirectHelper = $this->createMock(Redirect::class);
        $this->url = $this->createMock(Url::class);
        $this->authChallengeContainer = $this->createMock(AuthChallengeContainer::class);
    }

    /**
     * @test
     */
    public function indexActionIsCallable()
    {
        // Setup
        $controller = $this->setUpSut();

        // Assert
        $this->assertIsCallable([$controller, 'indexAction']);
    }

    /**
     * @test
     * @depends indexActionIsCallable
     */
    public function indexActionRedirectsToDashboardWhenUserAlreadyLoggedIn()
    {
        $identity = $this->createMock(User::class);
        $identity->method('isAnonymous')->willReturn(true);
        $this->currentUser->method('getIdentity')->willReturn($identity);

        $this->formHelper->method('createForm')->willReturn($this->createMock(Form::class));

        // Setup
        $controller = $this->setUpSut();

        // Expect
        $this->redirectHelper->method('toRoute')->with(LoginController::ROUTE_DASHBOARD)->willReturn($this->redirect());

        // Execute
        $controller->indexAction();
    }

    /**
     * @test
     */
    public function indexActionReturnsViewModel()
    {
        // Setup
        $identity = $this->createMock(User::class);
        $identity->method('isAnonymous')->willReturn(true);
        $this->currentUser->method('getIdentity')->willReturn($identity);

        $this->formHelper->method('createForm')->willReturn($this->createMock(Form::class));

        $controller = $this->setUpSut();

        // Execute
        $result = $controller->indexAction();

        // Assert
        $this->assertInstanceOf(ViewModel::class, $result);
    }

    /**
     * @test
     * @depends indexAction_ReturnsViewModel
     */
    public function indexActionReturnsViewModelWithLoginForm()
    {
        // Setup
        $identity = $this->createMock(User::class);
        $identity->method('isAnonymous')->willReturn(true);
        $this->currentUser->method('getIdentity')->willReturn($identity);

        $this->formHelper->method('createForm')->willReturn($this->createMock(Form::class));

        $controller = $this->setUpSut();

        // Execute
        $result = $controller->indexAction();
        $form = $result->getVariable('form');

        // Assert
        $this->assertInstanceOf(Form::class, $form);
    }

    /**
     * @test
     * @depends indexAction_ReturnsViewModel
     */
    public function indexActionReturnsViewModelWithFailureReasonWhenAuthenticationFails()
    {
        // Setup
        $identity = $this->createMock(User::class);
        $identity->method('isAnonymous')->willReturn(true);
        $this->currentUser->method('getIdentity')->willReturn($identity);

        $form = $this->createMock(Form::class);
        $form->method('isValid')->willReturn(true);
        $form->method('getData')->willReturn([]);
        $this->formHelper->method('createForm')->willReturn($form);

        $controller = $this->setUpSut();

        $this->flashMessenger->method('hasMessages')
            ->with(
                $this->logicalOr(
                    $this->equalTo(LoginController::FLASH_MESSAGE_NAMESPACE_INPUT),
                    $this->equalTo(LoginController::FLASH_MESSAGE_NAMESPACE_AUTH_ERROR)
                )
            )
            ->willReturn(true);

        $this->flashMessenger->method('getMessages')
            ->with(LoginController::FLASH_MESSAGE_NAMESPACE_INPUT)
            ->willReturn(['formData']);

        $this->flashMessenger->method('getMessagesFromNamespace')
            ->with(LoginController::FLASH_MESSAGE_NAMESPACE_AUTH_ERROR)
            ->willReturn(['failureReason']);

        // Execute
        $result = $controller->indexAction();

        // Assert
        $this->assertArrayHasKey('failureReason', $result->getVariables());
    }

    /**
     * @test
     */
    public function postActionIsCallable()
    {
        // Setup

        $controller = $this->setUpSut();

        // Assert
        $this->assertIsCallable([$controller, 'postAction']);
    }

    /**
     * @test
     * @depends postActionIsCallable
     */
    protected function postActionRedirectsToDashboardWhenUserAlreadyLoggedIn()
    {
        $identity = $this->createMock(User::class);
        $identity->method('isAnonymous')->willReturn(true);

        // Setup
        $controller = $this->setUpSut();
        $this->currentUser->method('getIdentity')->willReturn($identity);

        $this->redirectHelper->method('toRoute')->with(LoginController::ROUTE_DASHBOARD)->willReturn($this->redirect());

        // Execute
        $controller->postAction($this->postRequest(), new RouteMatch([]), new Response());
    }

    /**
     * @test
     * @depends postActionIsCallable
     */
    public function postActionFlashesFormDataWhenFormInvalid()
    {
        // Setup
        $identity = $this->createMock(User::class);
        $identity->method('isAnonymous')->willReturn(true);
        $this->currentUser->method('getIdentity')->willReturn($identity);

        $form = $this->createMock(Form::class);
        $form->method('isValid')->willReturn(true);
        $form->method('getData')->willReturn([]);
        $this->formHelper->method('createForm')->willReturn($form);

        $controller = $this->setUpSut();

        $this->redirectHelper->method('toRoute')->willReturn($this->redirect());

        // Expect
        $this->flashMessenger
            ->method('addMessage')
            ->with(
                $this->logicalOr($this->equalTo('[]'), LoginController::TRANSLATION_KEY_SUFFIX_AUTH_INVALID_USERNAME_OR_PASSWORD),
                $this->logicalOr(LoginController::FLASH_MESSAGE_NAMESPACE_AUTH_ERROR, LoginController::FLASH_MESSAGE_NAMESPACE_INPUT)
            );

        // Execute
        $controller->postAction($this->postRequest(), new RouteMatch([]), new Response());
    }

    /**
     * @test
     * @depends postAction_IsCallable
     */
    public function postActionSuccessfulCognitoAuthRedirectsToDashboard()
    {
        // Setup
        $identity = $this->createMock(User::class);
        $identity->method('isAnonymous')->willReturn(true);
        $this->currentUser->method('getIdentity')->willReturn($identity);

        $form = $this->createMock(Form::class);
        $form->method('isValid')->willReturn(true);
        $form->method('getData')->willReturn([]);
        $this->formHelper->method('createForm')->willReturn($form);

        $controller = $this->setUpSut();
        $request = $this->postRequest(['username' => 'username', 'password' => 'password', 'declarationRead' => 'Y']);
        $response = new Response();

        $this->authenticationService->method('authenticate')->willReturn(new Result(...static::AUTHENTICATION_RESULT_SUCCESSFUL_COGNITO));
        $this->redirectHelper->method('toRoute')->with(LoginController::ROUTE_DASHBOARD)->willReturn($this->redirect());

        // Execute
        $controller->postAction($request, new RouteMatch([]), $response);
    }

    /**
     * @test
     * @depends postActionIsCallable
     */
    public function postActionUnknownProviderRedirectsToLogin()
    {
        // Setup
        $identity = $this->createMock(User::class);
        $identity->method('isAnonymous')->willReturn(true);
        $this->currentUser->method('getIdentity')->willReturn($identity);

        $this->formHelper->method('createForm')->willReturn($this->createMock(Form::class));

        $controller = $this->setUpSut();
        $request = $this->postRequest(['username' => 'username', 'password' => 'password', 'declarationRead' => 'Y']);
        $response = new Response();

        $this->authenticationService->method('authenticate')->willReturn(new Result(...static::AUTHENTICATION_RESULT_SUCCESSFUL_UNKNOW_PROVIDER));
        $this->redirectHelper->method('toRoute')->with(LoginController::ROUTE_AUTH_LOGIN_GET)->willReturn($this->redirect());

        // Execute
        $controller->postAction($request, new RouteMatch([]), $response);
    }

    /**
     * @test
     * @depends postActionIsCallable
     */
    public function postActionNewPasswordRequiredChallengeStoresChallengeInSession()
    {
        // Setup
        $identity = $this->createMock(User::class);
        $identity->method('isAnonymous')->willReturn(true);
        $this->currentUser->method('getIdentity')->willReturn($identity);

        $form = $this->createMock(Form::class);
        $form->method('isValid')->willReturn(true);
        $form->method('getData')->willReturn([]);
        $this->formHelper->method('createForm')->willReturn($form);

        $controller = $this->setUpSut();
        $request = $this->postRequest(['username' => 'username', 'password' => 'password', 'declarationRead' => 'Y']);

        $this->authenticationService->method('authenticate')->willReturn(new Result(...static::AUTHENTICATION_RESULT_CHALLENGE_NEW_PASSWORD_REQUIRED));
        $this->redirectHelper->method('toRoute')->with(LoginController::ROUTE_AUTH_EXPIRED_PASSWORD)->willReturn($this->redirect());

        $this->authChallengeContainer->method('setChallengeName')->willReturnSelf();
        $this->authChallengeContainer->method('setChallengeSession')->willReturnSelf();
        $this->authChallengeContainer->method('setChallengedIdentity')->willReturnSelf();

        // Expect
        $this->redirectHelper->method('toRoute')->with(LoginController::ROUTE_AUTH_EXPIRED_PASSWORD)->willReturn($this->redirect());

        // Execute
        $controller->postAction($request, new RouteMatch([]), new Response());
    }

    /**
     * @test
     * @depends postActionNewPasswordRequiredChallengeStoresChallengeInSession
     */
    public function postActionNewPasswordRequiredChallengeRedirectsToExpiredPassword()
    {
        // Setup
        $controller = $this->setUpSut();
        $request = $this->postRequest(['username' => 'username', 'password' => 'password', 'declarationRead' => 'Y']);

        $this->authenticationService->method('authenticate')->willReturn(new Result(...static::AUTHENTICATION_RESULT_CHALLENGE_NEW_PASSWORD_REQUIRED));
        $this->redirectHelper->method('toRoute')->with(LoginController::ROUTE_AUTH_LOGIN_GET)->willReturn($this->redirect());

        $this->authChallengeContainer->method('setChallengeName')->willReturnSelf();
        $this->authChallengeContainer->method('setChallengeSession')->willReturnSelf();
        $this->authChallengeContainer->method('setChallengedIdentity')->willReturnSelf();

        // Expect
        $this->redirectHelper->method('toRoute')->with(LoginController::ROUTE_AUTH_EXPIRED_PASSWORD, ['USER_ID_FOR_SRP' => 'username'])->willReturn($this->redirect());

        // Execute
        $controller->postAction($request, new RouteMatch([]), new Response());
    }

    /**
     * @test
     * @depends postActionIsCallable
     */
    public function postActionUnsupportedChallengeRedirectsToLoginPage()
    {
        // Setup
        $identity = $this->createMock(User::class);
        $identity->method('isAnonymous')->willReturn(true);
        $this->currentUser->method('getIdentity')->willReturn($identity);

        $this->formHelper->method('createForm')->willReturn($this->createMock(Form::class));

        $controller = $this->setUpSut();
        $request = $this->postRequest(['username' => 'username', 'password' => 'password', 'declarationRead' => 'Y']);

        $this->authenticationService->method('authenticate')->willReturn(new Result(...static::AUTHENTICATION_RESULT_CHALLENGE_UNSUPPORTED));
        $this->redirectHelper->method('toRoute')->with(LoginController::ROUTE_AUTH_LOGIN_GET)->willReturn($this->redirect());

        // Execute
        $controller->postAction($request, new RouteMatch([]), new Response());
    }

    /**
     * @test
     * @depends postActionIsCallable
     */
    public function postActionFailedAuthenticationRedirectsToLoginPage()
    {
        // Setup
        $identity = $this->createMock(User::class);
        $identity->method('isAnonymous')->willReturn(true);
        $this->currentUser->method('getIdentity')->willReturn($identity);

        $this->formHelper->method('createForm')->willReturn($this->createMock(Form::class));

        $controller = $this->setUpSut();
        $request = $this->postRequest(['username' => 'username', 'password' => 'password', 'declarationRead' => 'Y']);

        $this->authenticationService->method('authenticate')->willReturn(new Result(...static::AUTHENTICATION_RESULT_FAILURE));
        $this->redirectHelper->method('toRoute')->with(LoginController::ROUTE_AUTH_LOGIN_GET)->willReturn($this->redirect());

        // Execute
        $controller->postAction($request, new RouteMatch([]), new Response());
    }

    /**
     * @test
     * @depends postActionIsCallable
     */
    public function postActionFailedAuthenticationFlashesInvalidUsernameOrPasswordByDefault()
    {
        // Setup
        $identity = $this->createMock(User::class);
        $identity->method('isAnonymous')->willReturn(true);
        $this->currentUser->method('getIdentity')->willReturn($identity);

        $form = $this->createMock(Form::class);
        $form->method('isValid')->willReturn(true);
        $form->method('getData')->willReturn([]);
        $this->formHelper->method('createForm')->willReturn($form);

        $controller = $this->setUpSut();
        $request = $this->postRequest(['username' => 'username', 'password' => 'password', 'declarationRead' => 'Y']);

        $this->authenticationService->method('authenticate')->willReturn(new Result(...static::AUTHENTICATION_RESULT_FAILURE));
        $this->redirectHelper->method('toRoute')->with(LoginController::ROUTE_AUTH_LOGIN_GET)->willReturn($this->redirect());

        // Expect
        $this->flashMessenger
            ->method('addMessage')
            ->with(
                $this->logicalOr($this->equalTo('[]'), LoginController::TRANSLATION_KEY_SUFFIX_AUTH_INVALID_USERNAME_OR_PASSWORD),
                $this->logicalOr(LoginController::FLASH_MESSAGE_NAMESPACE_AUTH_ERROR, LoginController::FLASH_MESSAGE_NAMESPACE_INPUT)
            );

        // Execute
        $controller->postAction($request, new RouteMatch([]), new Response());
    }

    /**
     * @test
     * @depends postActionIsCallable
     */
    public function postActionFailedAuthenticationFlashesInvalidUsernameOrPasswordWhenUserNotExists()
    {
        // Setup
        $identity = $this->createMock(User::class);
        $identity->method('isAnonymous')->willReturn(true);
        $this->currentUser->method('getIdentity')->willReturn($identity);

        $form = $this->createMock(Form::class);
        $form->method('isValid')->willReturn(true);
        $form->method('getData')->willReturn([]);
        $this->formHelper->method('createForm')->willReturn($form);

        $controller = $this->setUpSut();
        $request = $this->postRequest(['username' => 'username', 'password' => 'password', 'declarationRead' => 'Y']);

        $this->authenticationService->method('authenticate')->willReturn(new Result(...static::AUTHENTICATION_RESULT_USER_NOT_EXIST));
        $this->redirectHelper->method('toRoute')->with(LoginController::ROUTE_AUTH_LOGIN_GET)->willReturn($this->redirect());

        // Expect
        $this->flashMessenger
            ->method('addMessage')
            ->with(
                $this->logicalOr($this->equalTo('[]'), LoginController::TRANSLATION_KEY_SUFFIX_AUTH_INVALID_USERNAME_OR_PASSWORD),
                $this->logicalOr(LoginController::FLASH_MESSAGE_NAMESPACE_AUTH_ERROR, LoginController::FLASH_MESSAGE_NAMESPACE_INPUT)
            );

        // Execute
        $controller->postAction($request, new RouteMatch([]), new Response());
    }

    /**
     * @test
     * @depends postActionIsCallable
     */
    public function postActionFailedAuthenticationFlashesInvalidUsernameOrPasswordWhenPasswordIncorrect()
    {
        // Setup
        $identity = $this->createMock(User::class);
        $identity->method('isAnonymous')->willReturn(true);
        $this->currentUser->method('getIdentity')->willReturn($identity);

        $form = $this->createMock(Form::class);
        $form->method('isValid')->willReturn(true);
        $form->method('getData')->willReturn([]);
        $this->formHelper->method('createForm')->willReturn($form);

        $controller = $this->setUpSut();
        $request = $this->postRequest(['username' => 'username', 'password' => 'password', 'declarationRead' => 'Y']);

        $this->authenticationService->method('authenticate')->willReturn(new Result(...static::AUTHENTICATION_RESULT_CREDENTIAL_INVALID));
        $this->redirectHelper->method('toRoute')->with(LoginController::ROUTE_AUTH_LOGIN_GET)->willReturn($this->redirect());

        // Expect
        $this->flashMessenger
            ->method('addMessage')
            ->with(
                $this->logicalOr($this->equalTo('[]'), LoginController::TRANSLATION_KEY_SUFFIX_AUTH_INVALID_USERNAME_OR_PASSWORD),
                $this->logicalOr(LoginController::FLASH_MESSAGE_NAMESPACE_AUTH_ERROR, LoginController::FLASH_MESSAGE_NAMESPACE_INPUT)
            );

        // Execute
        $controller->postAction($request, new RouteMatch([]), new Response());
    }

    /**
     * @test
     * @depends postActionIsCallable
     */
    public function postActionFailedAuthenticationFlashesAccountDisabledWhenAuthenticationResultIsFailureAccountDisabled()
    {
        // Setup
        $identity = $this->createMock(User::class);
        $identity->method('isAnonymous')->willReturn(true);
        $this->currentUser->method('getIdentity')->willReturn($identity);

        $form = $this->createMock(Form::class);
        $form->method('isValid')->willReturn(true);
        $form->method('getData')->willReturn([]);
        $this->formHelper->method('createForm')->willReturn($form);

        $controller = $this->setUpSut();
        $request = $this->postRequest(['username' => 'username', 'password' => 'password', 'declarationRead' => 'Y']);

        $this->authenticationService->method('authenticate')->willReturn(new Result(...static::AUTHENTICATION_RESULT_FAILURE_ACCOUNT_DISABLED));
        $this->redirectHelper->method('toRoute')->with(LoginController::ROUTE_AUTH_LOGIN_GET)->willReturn($this->redirect());

        // Expect
        $this->flashMessenger
            ->method('addMessage')
            ->with(
                $this->logicalOr($this->equalTo('[]'), LoginController::TRANSLATION_KEY_SUFFIX_AUTH_ACCOUNT_DISABLED),
                $this->logicalOr(LoginController::FLASH_MESSAGE_NAMESPACE_AUTH_ERROR, LoginController::FLASH_MESSAGE_NAMESPACE_INPUT)
            );

        // Execute
        $controller->postAction($request, new RouteMatch([]), new Response());
    }

    /**
     * @return LoginController
     */
    protected function setUpSut()
    {
        return new LoginController(
            $this->authenticationAdapter,
            $this->authenticationService,
            $this->currentUser,
            $this->flashMessenger,
            $this->formHelper,
            $this->layout,
            $this->redirectHelper,
            $this->url,
            $this->authChallengeContainer
        );
    }

    /**
     * @return HttpResponse
     */
    protected function redirect(): HttpResponse
    {
        $response = new HttpResponse();
        $response->setStatusCode(HttpResponse::STATUS_CODE_302);
        return $response;
    }

    /**
     * @param array|null $data
     * @return Request
     */
    protected function postRequest(array $data = null, array $query = null): Request
    {
        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $request->setPost(new Parameters($data ?? static::EMPTY_FORM_DATA));
        $request->setQuery(new Parameters($query ?? []));
        $request->setUri('https://localhost');
        return $request;
    }
}
