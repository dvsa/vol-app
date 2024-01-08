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
use Laminas\Mvc\Controller\Plugin\FlashMessenger;
use Laminas\Mvc\Controller\Plugin\Layout;
use Laminas\Mvc\Controller\Plugin\Url;
use Laminas\Mvc\Router\Http\RouteMatch;
use Laminas\Stdlib\Parameters;
use Laminas\View\Model\ViewModel;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Auth\Adapter\InternalCommandAdapter;
use Olcs\Controller\Auth\LoginController;

class LoginControllerTest extends MockeryTestCase
{
    const EMPTY_FORM_DATA = [
        'username' => null,
        'password' => null,
        'csrf' => null,
    ];

    const AUTHENTICATION_RESULT_SUCCESSFUL_OPENAM = [
        Result::SUCCESS,
        [
            'provider' => LoginController::DVSA_OLCS_AUTH_CLIENT_OPENAM,
            'tokenId' => 'tokenId'
        ],
        []
    ];
    const AUTHENTICATION_RESULT_CHALLENGE_NEW_PASSWORD_REQUIRED = [
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
    const AUTHENTICATION_RESULT_CHALLENGE_UNSUPPORTED = [
        LoginController::AUTH_SUCCESS_WITH_CHALLENGE,
        [],
        [
            'challengeName' => 'UnsupportedChallenge',
        ]
    ];
    const AUTHENTICATION_RESULT_FAILURE = [Result::FAILURE, [], ['failed']];
    const AUTHENTICATION_RESULT_USER_NOT_EXIST = [Result::FAILURE_IDENTITY_NOT_FOUND, [], ['Authentication Failed']];
    const AUTHENTICATION_RESULT_CREDENTIAL_INVALID = [Result::FAILURE_CREDENTIAL_INVALID, [], ['Authentication Failed']];
    const AUTHENTICATION_RESULT_FAILURE_ACCOUNT_DISABLED = [LoginController::AUTH_FAILURE_ACCOUNT_DISABLED, [], ['account-disabled']];
    const AUTHENTICATION_RESULT_SUCCESSFUL_COGNITO = [Result::SUCCESS, ['provider' => LoginController::DVSA_OLCS_AUTH_CLIENT_COGNITO]];
    const AUTHENTICATION_RESULT_SUCCESSFUL_UNKNOW_PROVIDER = [Result::SUCCESS, [ 'provider' => 'unknown']];

    /**
     * @var LoginController
     */
    protected $sut;

    public function setUp(): void
    {
        $this->authenticationAdapter = $this->createMock(InternalCommandAdapter::class);
        $this->authenticationService = $this->createMock(AuthenticationServiceInterface::class);
        $this->cookieService = $this->createMock(CookieService::class);
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
    public function indexAction_IsCallable()
    {
        // Setup
        $controller = $this->setUpSut();

        // Assert
        $this->assertIsCallable([$controller, 'indexAction']);
    }

    /**
     * @test
     * @depends indexAction_IsCallable
     */
    public function indexAction_RedirectsToDashboard_WhenUserAlreadyLoggedIn()
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
    public function indexAction_ReturnsViewModel()
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
    public function indexAction_ReturnsViewModel_WithLoginForm()
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
    public function indexAction_ReturnsViewModel_WithFailureReason_WhenAuthenticationFails()
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
    public function postAction_IsCallable()
    {
        // Setup

        $controller = $this->setUpSut();

        // Assert
        $this->assertIsCallable([$controller, 'postAction']);
    }

    /**
     * @test
     * @depends postAction_IsCallable
     */
    protected function postAction_RedirectsToDashboard_WhenUserAlreadyLoggedIn()
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
     * @depends postAction_IsCallable
     */
    public function postAction_FlashesFormData_WhenFormInvalid()
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
    public function postAction_SuccessfullOpenAMAuth_SetsCookie()
    {
        // Setup
        $identity = $this->createMock(User::class);
        $identity->method('isAnonymous')->willReturn(true);
        $this->currentUser->method('getIdentity')->willReturn($identity);

        $this->formHelper->method('createForm')->willReturn($this->createMock(Form::class));

        $controller = $this->setUpSut();
        $request = $this->postRequest(['username' => 'username', 'password' => 'password', 'declarationRead' => 'Y']);
        $response = new Response();

        $this->authenticationService->method('authenticate')->willReturn(new Result(...static::AUTHENTICATION_RESULT_SUCCESSFUL_OPENAM));
        $this->cookieService->method('createTokenCookie')->with($response, 'tokenId', false);
        $this->redirectHelper->method('toRoute')->willReturn($this->redirect());

        // Execute
        $controller->postAction($request, new RouteMatch([]), $response);
    }

    /**
     * @test
     * @depends postAction_SuccessfullOpenAMAuth_SetsCookie
     */
    public function postAction_SuccessfullOpenAMAuth_RedirectsToDashBoard_WhenGotoNotPresent()
    {
        // Setup
        $controller = $this->setUpSut();
        $request = $this->postRequest(['username' => 'username', 'password' => 'password', 'declarationRead' => 'Y']);
        $response = new Response();

        $this->authenticationService->method('authenticate')->willReturn(new Result(...static::AUTHENTICATION_RESULT_SUCCESSFUL_OPENAM));
        $this->cookieService->method('createTokenCookie')->with($response, 'tokenId', false);
        $this->redirectHelper->method('toRoute')->with(LoginController::ROUTE_DASHBOARD)->willReturn($this->redirect());

        // Execute
        $controller->postAction($request, new RouteMatch([]), $response);
    }

    /**
     * @test
     * @depends postAction_SuccessfullOpenAMAuth_SetsCookie
     */
    public function postAction_SuccessfullOpenAMAuth_RedirectsToGoto_WhenPresentAndValid()
    {
        // Setup
        $controller = $this->setUpSut();
        $request = $this->postRequest(
            ['username' => 'username', 'password' => 'password', 'declarationRead' => 'Y'],
            ['goto' => 'https://localhost/goto/url']
        );
        $response = new Response();

        $this->authenticationService->method('authenticate')->willReturn(new Result(...static::AUTHENTICATION_RESULT_SUCCESSFUL_OPENAM));
        $this->cookieService->method('createTokenCookie')->with($response, 'tokenId', false);
        $this->redirectHelper->method('toUrl')->with('https://localhost/goto/url')->willReturn($this->redirect());

        // Execute
        $controller->postAction($request, new RouteMatch([]), $response);
    }

    /**
     * @test
     * @depends postAction_SuccessfullOpenAMAuth_SetsCookie
     */
    public function postAction_SuccessfullOpenAMAuth_RedirectsToDashboard_WhenGotoPresentAndInvalid()
    {
        // Setup
        $controller = $this->setUpSut();
        $request = $this->postRequest(
            ['username' => 'username', 'password' => 'password', 'declarationRead' => 'Y'],
            ['goto' => 'https://example.com/goto/url']
        );
        $response = new Response();

        $this->authenticationService->method('authenticate')->willReturn(new Result(...static::AUTHENTICATION_RESULT_SUCCESSFUL_OPENAM));
        $this->cookieService->method('createTokenCookie')->with($response, 'tokenId', false);
        $this->redirectHelper->method('toRoute')->with(LoginController::ROUTE_DASHBOARD)->willReturn($this->redirect());

        // Execute
        $controller->postAction($request, new RouteMatch([]), $response);
    }

    /**
     * @test
     * @depends postAction_SuccessfullOpenAMAuth_SetsCookie
     */
    public function postAction_SuccessfullOpenAMAuth_RedirectsHttps_WhenGotoIsHttp()
    {
        // Setup
        $controller = $this->setUpSut();
        $request = $this->postRequest(
            ['username' => 'username', 'password' => 'password', 'declarationRead' => 'Y'],
            ['goto' => 'http://localhost/goto/url']
        );
        $response = new Response();

        $this->authenticationService->method('authenticate')->willReturn(new Result(...static::AUTHENTICATION_RESULT_SUCCESSFUL_OPENAM));
        $this->cookieService->method('createTokenCookie')->with($response, 'tokenId', false);
        $this->redirectHelper->method('toUrl')->with('https://localhost/goto/url')->willReturn($this->redirect());

        // Execute
        $controller->postAction($request, new RouteMatch([]), $response);
    }

    /**
     * @test
     * @depends postAction_IsCallable
     */
    public function postAction_SuccessfulCognitoAuth_RedirectsToDashboard()
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
     * @depends postAction_IsCallable
     */
    public function postAction_UnknownProvider_RedirectsToLogin()
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
     * @depends postAction_IsCallable
     */
    public function postAction_NewPasswordRequiredChallenge_StoresChallengeInSession()
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
        $this->redirectHelper->method('toRoute')->with(LoginController::ROUTE_AUTH_EXPIRED_PASSWORD, ['USER_ID_FOR_SRP' => 'username'])->willReturn($this->redirect());

        // Execute
        $controller->postAction($request, new RouteMatch([]), new Response());
    }

    /**
     * @test
     * @depends postAction_NewPasswordRequiredChallenge_StoresChallengeInSession
     */
    public function postAction_NewPasswordRequiredChallenge_RedirectsToExpiredPassword()
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
     * @depends postAction_IsCallable
     */
    public function postAction_UnsupportedChallenge_RedirectsToLoginPage()
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
     * @depends postAction_IsCallable
     */
    public function postAction_FailedAuthentication_RedirectsToLoginPage()
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
     * @depends postAction_IsCallable
     */
    public function postAction_FailedAuthentication_FlashesInvalidUsernameOrPasswordByDefault()
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
     * @depends postAction_IsCallable
     */
    public function postAction_FailedAuthentication_FlashesInvalidUsernameOrPasswordWhenUserNotExists()
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
     * @depends postAction_IsCallable
     */
    public function postAction_FailedAuthentication_FlashesInvalidUsernameOrPasswordWhenPasswordIncorrect()
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
     * @depends postAction_IsCallable
     */
    public function postAction_FailedAuthentication_FlashesAccountDisabledWhenAuthenticationResult_IsFailureAccountDisabled()
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
            $this->cookieService,
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
