<?php

namespace OlcsTest\Listener;

use Common\Rbac\User as RbacUser;
use Common\Service\Cqrs\Query\QuerySender;
use Olcs\Controller\Listener\Navigation as NavigationListener;
use Mockery as m;
use Laminas\Http\Header\Referer as HttpReferer;
use Laminas\Http\PhpEnvironment\Request as HttpRequest;
use Laminas\Navigation\Navigation;
use Laminas\Navigation\Page\Uri;
use Laminas\Mvc\MvcEvent;

/**
 * Class NavigationToggleTest
 * @package OlcsTest\Listener
 */
class NavigationTest extends m\Adapter\Phpunit\MockeryTestCase
{
    /** @var NavigationListener */
    protected $sut;

    /** @var Navigation|m\MockInterface */
    private $mockNavigation;

    /** @var QuerySender|m\MockInterface */
    private $mockQuerySender;

    /** @var RbacUser|m\MockInterface */
    private $mockIdentity;

    public function setUp(): void
    {
        $this->mockNavigation = m::mock(Navigation::class);
        $this->mockQuerySender = m::mock(QuerySender::class);
        $this->mockIdentity = m::mock(RbacUser::class);
        $this->sut = new NavigationListener($this->mockNavigation, $this->mockQuerySender, $this->mockIdentity);
    }

    public function testAttach()
    {
        /** @var \Laminas\EventManager\EventManagerInterface | m\MockInterface $mockEventManager */
        $mockEventManager = m::mock(\Laminas\EventManager\EventManagerInterface::class);
        $mockEventManager->shouldReceive('attach')->once()
            ->with(MvcEvent::EVENT_DISPATCH, [$this->sut, 'onDispatch'], 20);

        $this->sut->attach($mockEventManager);
    }

    public function testOnDispatchWithNoReferalAnonymousUser()
    {
        $dashboardPermitsKey = 'dashboard-permits';
        $dashboardPermitsPage = new Uri();

        $this->mockIdentity->shouldReceive('isAnonymous')->once()->withNoArgs()->andReturn(true);

        $this->mockNavigation
            ->shouldReceive('findBy')
            ->with('id', $dashboardPermitsKey)
            ->twice()
            ->andReturn($dashboardPermitsPage);

        $request = m::mock(HttpRequest::class);
        $request->shouldReceive('getHeader')->once()->with('referer')->andReturn(false);

        /** @var \Laminas\Mvc\MvcEvent | m\MockInterface $mockEvent */
        $mockEvent = m::mock(\Laminas\Mvc\MvcEvent::class);
        $mockEvent->shouldReceive('getRequest')->once()->withNoArgs()->andReturn($request);

        $this->sut->onDispatch($mockEvent);

        $this->assertEquals(
            false,
            $this->mockNavigation->findBy('id', $dashboardPermitsKey)->getVisible()
        );
    }

    /**
     * @dataProvider dpDispatchNoReferer
     */
    public function testOnDispatchWithNoReferal($eligibleForPermits)
    {
        $dashboardPermitsKey = 'dashboard-permits';
        $dashboardPermitsPage = new Uri();

        $this->mockIdentity->shouldReceive('isAnonymous')->once()->withNoArgs()->andReturn(false);
        $this->mockIdentity->expects('getUserData')
            ->andReturn(['eligibleForPermits' => $eligibleForPermits]);

        $this->mockNavigation
            ->shouldReceive('findBy')
            ->with('id', $dashboardPermitsKey)
            ->twice()
            ->andReturn($dashboardPermitsPage);

        $request = m::mock(HttpRequest::class);
        $request->shouldReceive('getHeader')->once()->with('referer')->andReturn(false);

        /** @var \Laminas\Mvc\MvcEvent | m\MockInterface $mockEvent */
        $mockEvent = m::mock(\Laminas\Mvc\MvcEvent::class);
        $mockEvent->shouldReceive('getRequest')->once()->withNoArgs()->andReturn($request);

        $this->sut->onDispatch($mockEvent);

        $this->assertEquals(
            $eligibleForPermits,
            $this->mockNavigation->findBy('id', $dashboardPermitsKey)->getVisible()
        );
    }

    public function dpDispatchNoReferer()
    {
        return [
            [true],
            [false],
        ];
    }

    public function testOnDispatchWithGovUkReferalMatch()
    {
        $dashboardPermitsKey = 'dashboard-permits';
        $dashboardPermitsPage = new Uri();
        $uri = 'uri';
        $this->sut->setGovUkReferers([$uri]);

        //mock the http referer - this will be checked against our list of gov.uk referers (and will match)
        $referer = m::mock(HttpReferer::class);
        $referer->shouldReceive('getUri')->once()->withNoArgs()->andReturn($uri);
        $request = m::mock(HttpRequest::class);
        $request->shouldReceive('getHeader')->once()->with('referer')->andReturn($referer);

        $this->mockNavigation
            ->shouldReceive('findBy')
            ->twice()
            ->with('id', $dashboardPermitsKey)
            ->andReturn($dashboardPermitsPage);

        /** @var \Laminas\Mvc\MvcEvent | m\MockInterface $mockEvent */
        $mockEvent = m::mock(\Laminas\Mvc\MvcEvent::class);
        $mockEvent->shouldReceive('getRequest')->once()->withNoArgs()->andReturn($request);

        $this->sut->onDispatch($mockEvent);

        $this->assertTrue(
            $this->mockNavigation->findBy('id', $dashboardPermitsKey)->getVisible()
        );
    }

    /**
     * @dataProvider dpDispatchWithoutMatchedReferer
     */
    public function testOnDispatchWithNoGovUkReferal($eligibleForPermits)
    {
        $dashboardPermitsKey = 'dashboard-permits';
        $dashboardPermitsPage = new Uri();

        $this->mockIdentity->shouldReceive('isAnonymous')
            ->once()
            ->withNoArgs()
            ->andReturn(false);

        $this->mockIdentity->expects('getUserData')
            ->andReturn(['eligibleForPermits' => $eligibleForPermits]);

        $this->mockNavigation
            ->shouldReceive('findBy')
            ->twice()
            ->with('id', $dashboardPermitsKey)
            ->andReturn($dashboardPermitsPage);

        //mock the http referer - this will be checked against our list of gov.uk referers (and won't match)
        $referer = m::mock(HttpReferer::class);
        $referer->shouldReceive('getUri')->once()->withNoArgs()->andReturn('uri');
        $request = m::mock(HttpRequest::class);
        $request->shouldReceive('getHeader')->once()->with('referer')->andReturn($referer);

        /** @var \Laminas\Mvc\MvcEvent | m\MockInterface $mockEvent */
        $mockEvent = m::mock(\Laminas\Mvc\MvcEvent::class);
        $mockEvent->shouldReceive('getRequest')->once()->withNoArgs()->andReturn($request);

        $this->sut->onDispatch($mockEvent);

        $this->assertEquals(
            $eligibleForPermits,
            $this->mockNavigation->findBy('id', $dashboardPermitsKey)->getVisible()
        );
    }

    public function dpDispatchWithoutMatchedReferer()
    {
        return [
            [true],
            [false],
        ];
    }
}
