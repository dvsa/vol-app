<?php

declare(strict_types=1);

namespace OlcsTest\Controller;

use Common\Controller\Plugin\Redirect;
use Common\Rbac\JWTIdentityProvider;
use Common\Rbac\User;
use Interop\Container\Containerinterface;
use Laminas\Http\Request;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\Router\RouteMatch;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\Stdlib\Parameters;
use Laminas\Uri\Http;
use Laminas\View\Model\ViewModel;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\SessionTimeoutController;
use LmcRbacMvc\Identity\IdentityProviderInterface;

/**
 * @see SessionTimeoutController
 */
class SessionTimeoutControllerTest extends MockeryTestCase
{
    protected const COOKIE_NAME = 'cookie';
    private $identityProviderClass = JWTIdentityProvider::class;
    private IdentityProviderInterface $identityProviderMock;
    protected Redirect $redirectHelperMock;
    protected SessionTimeoutController $sut;

    /**
     * @test
     */
    public function indexActionIsCallable(): void
    {
        $this->setUpSut();
        // Assert
        $this->assertTrue(method_exists($this->sut, 'indexAction') && is_callable($this->sut->indexAction(...)));
    }

    /**
     * @test
     */
    public function indexActionReturnsViewModelIfIdentityIsAnonymous(): void
    {
        $this->setUpSut();
        // Define Expectations
        $identity = m::mock(User::class);
        $identity->shouldReceive('isAnonymous')->andReturnTrue();
        $this->identityProviderMock->shouldReceive('getIdentity')->withNoArgs()->andReturn($identity);

        // Execute
        $result = $this->sut->indexAction($this->setUpRequest());

        // Assert
        $this->assertInstanceOf(ViewModel::class, $result);
    }

    /**
     * @test
     */
    public function indexActionReturnsViewModelIfIdentityIsNull(): void
    {
        $this->setUpSut();
        // Define Expectations
        $this->identityProviderMock->shouldReceive('getIdentity')->withNoArgs()->andReturnNull()->once();

        // Execute
        $result = $this->sut->indexAction($this->setUpRequest());

        // Assert
        $this->assertInstanceOf(ViewModel::class, $result);
    }

    /**
     * @test
     */
    public function indexActionLogsOutUserIfLoggedIn(): void
    {
        $this->setUpSut();
        //setup
        $request = $this->setUpRequest();

        //Define Expectation
        $this->redirectHelperMock->shouldReceive('refresh')
            ->withNoArgs()
            ->andReturn($expectedResponse = new Response())
            ->once();

        $this->setUpIdentityWithClearSession();

        // Execute
        $response = $this->sut->indexAction($request);

        // Assert
        $this->assertInstanceOf(Response::class, $response);
    }

    /**
     * @test
     *
     * @dataProvider dpIdentityProviderClass
     */
    public function indexActionRedirectsUserIfLoggedIn(string $identityProviderClass): void
    {
        $this->setUpSut();
        // Setup
        $request = $this->setUpRequest();

        $this->setUpIdentityWithClearSession();

        // Define Expectations
        $this->redirectHelperMock->shouldReceive('refresh')
            ->withNoArgs()
            ->andReturn($expectedResponse = new Response())
            ->once();

        // Execute
        $response = $this->sut->indexAction($request);

        // Assert
        $this->assertSame($expectedResponse, $response);
    }

    public function dpIdentityProviderClass(): array
    {
        return [
            [$this->identityProviderClass],
            [JWTIdentityProvider::class],
        ];
    }

    protected function setup(): void
    {
        $this->identityProviderMock = m::mock(IdentityProviderInterface::class);
        $this->redirectHelperMock = m::mock(Redirect::class);
    }

    /**
     * @param ServiceLocatorInterface $serviceLocator
     * @param Request $request
     * @param RouteMatch|null $routeMatch
     */
    protected function setUpSut(): void
    {
        $this->sut = new SessionTimeoutController(
            $this->identityProviderMock,
            $this->redirectHelperMock
        );
    }

    /**
     * @param string $url
     * @param array|null $input
     * @return Request
     */
    protected function setUpRequest(?string $url = null, array $input = null)
    {
        $uri = m::mock(Http::class);
        $uri->shouldIgnoreMissing($uri);
        $uri->shouldReceive('toString')->andReturn($url ?? 'foobarbaz');

        $request = new Request();
        $request->setUri($uri);
        $request->setQuery(new Parameters($input ?? []));

        return $request;
    }

    protected function setUpIdentityWithClearSession(): void
    {
        $identity = m::mock(User::class);
        $identity->expects('isAnonymous')
            ->withNoArgs()
            ->andReturnFalse();

        $this->identityProviderMock->expects('getIdentity')
            ->withNoArgs()
            ->andReturn($identity);
        $this->identityProviderMock->expects('clearSession')
            ->withNoArgs();
    }
}
