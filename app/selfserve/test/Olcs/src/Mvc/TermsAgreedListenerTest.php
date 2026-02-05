<?php

declare(strict_types=1);

namespace OlcsTest\Mvc;

use Common\Rbac\JWTIdentityProvider;
use Common\Rbac\User;
use Common\Service\Helper\UrlHelperService;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Http\Headers;
use Laminas\Http\Request;
use Laminas\Mvc\MvcEvent;
use Laminas\Stdlib\ResponseInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Mvc\TermsAgreedListener;
use PHPUnit\Framework\Attributes\DataProvider;

class TermsAgreedListenerTest extends MockeryTestCase
{
    private $sut;

    private $identityProvider;

    private $urlHelper;

    public function setUp(): void
    {
        $this->identityProvider = m::mock(JWTIdentityProvider::class);
        $this->urlHelper = m::mock(UrlHelperService::class);

        $this->sut = new TermsAgreedListener($this->identityProvider, $this->urlHelper);
    }

    public function testAttach(): void
    {
        $priority = 999;
        $em = m::mock(EventManagerInterface::class);
        $em->expects('attach')
            ->with(
                MvcEvent::EVENT_DISPATCH,
                m::on(function ($listener) {
                    $rf = new \ReflectionFunction($listener);
                    return $rf->getClosureThis() === $this->sut && $rf->getName() === 'onDispatch';
                }),
                $priority
            );

        $this->sut->attach($em, $priority);
    }

    public function testOnDispatchNonHttp(): void
    {
        $request = m::mock(\StdClass::class);
        $event = m::mock(MvcEvent::class);
        $event->expects('getRequest')->withNoArgs()->andReturn($request);

        $this->sut->onDispatch($event);
    }

    #[DataProvider('dpExcludedRoute')]
    public function testOnDispatchExcludedRoute(string $route): void
    {
        $this->sut->onDispatch(
            $this->getEvent($route)
        );
    }

    public static function dpExcludedRoute(): array
    {
        return [
            [TermsAgreedListener::ROUTE_WELCOME],
            ['auth/logout'],
            ['terms-and-conditions']
        ];
    }

    public function testOnDispatchAnonymousUser(): void
    {
        $user = m::mock(User::class);
        $user->expects('isAnonymous')->withNoArgs()->andReturnTrue();
        $this->identityProvider->expects('getIdentity')->withNoArgs()->andReturn($user);

        $this->sut->onDispatch(
            $this->getEvent()
        );
    }

    public function testOnDispatchNotIdentifiedUser(): void
    {
        $user = m::mock(User::class);
        $user->expects('isAnonymous')->withNoArgs()->andReturnFalse();
        $user->expects('isNotIdentified')->withNoArgs()->andReturnTrue();
        $this->identityProvider->expects('getIdentity')->withNoArgs()->andReturn($user);

        $this->sut->onDispatch(
            $this->getEvent()
        );
    }

    public function testOnDispatchTermsAgreed(): void
    {
        $user = m::mock(User::class);
        $user->expects('isAnonymous')->withNoArgs()->andReturnFalse();
        $user->expects('isNotIdentified')->withNoArgs()->andReturnFalse();
        $user->expects('hasAgreedTerms')->withNoArgs()->andReturnTrue();

        $this->identityProvider->expects('getIdentity')->withNoArgs()->andReturn($user);

        $this->sut->onDispatch(
            $this->getEvent()
        );
    }

    public function testOnDispatchTermsNotAgreed(): void
    {
        $user = m::mock(User::class);
        $user->expects('isAnonymous')->withNoArgs()->andReturnFalse();
        $user->expects('isNotIdentified')->withNoArgs()->andReturnFalse();
        $user->expects('hasAgreedTerms')->withNoArgs()->andReturnFalse();

        $this->identityProvider->expects('getIdentity')->withNoArgs()->andReturn($user);

        $redirectUrl = 'http://url';
        $this->urlHelper->expects('fromRoute')->with(TermsAgreedListener::ROUTE_WELCOME)->andReturn($redirectUrl);

        $responseHeaders = m::mock(Headers::class);
        $responseHeaders->expects('addHeaderLine')->with('Location', $redirectUrl);

        $response = m::mock(ResponseInterface::class);
        $response->expects('getHeaders')->withNoArgs()->andReturn($responseHeaders);
        $response->expects('setStatusCode')->with(303);

        $event = $this->getEvent();
        $event->expects('getResponse')->withNoArgs()->andReturn($response);

        $this->assertEquals($response, $this->sut->onDispatch($event));
    }

    private function getEvent(string $route = 'route'): m\MockInterface&m\LegacyMockInterface
    {
        $request = m::mock(Request::class);

        $event = m::mock(MvcEvent::class);
        $event->expects('getRequest')->withNoArgs()->andReturn($request);
        $event->expects('getRouteMatch->getMatchedRouteName')->withNoArgs()->andReturn($route);

        return $event;
    }
}
