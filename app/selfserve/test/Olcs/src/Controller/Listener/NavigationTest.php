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

        $this->dashboardPermitsKey = 'dashboard-permits';
        $this->dashboardPermitsPage = new Uri();

        $this->dashboardMessagingKey = 'dashboard-messaging';
        $this->dashboardMessagingPage = new Uri();
        $this->messagingToggle = 'messaging';
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
        $this->mockIdentity->shouldReceive('isAnonymous')->once()->withNoArgs()->andReturn(true);

        $this->mockIdentity->expects('getUserData')
            ->once()
            ->andReturn([
                'hasOrganisationSubmittedLicenceApplication' => false,
                'organisationUsers' => [
                    0 => [
                        'organisation' => [
                            'isMessagingDisabled' => false
                        ]
                    ]
                ]
            ]);

        $this->mockQuerySender->shouldReceive('featuresEnabled')->with([$this->messagingToggle])->once();

        $this->mockNavigation
            ->shouldReceive('findBy')
            ->with('id', $this->dashboardPermitsKey)
            ->twice()
            ->andReturn($this->dashboardPermitsPage);

        $this->mockNavigation
            ->shouldReceive('findBy')
            ->with('id', $this->dashboardMessagingKey)
            ->twice()
            ->andReturn($this->dashboardMessagingPage);

        $request = m::mock(HttpRequest::class);
        $request->shouldReceive('getHeader')->once()->with('referer')->andReturn(false);

        /** @var \Laminas\Mvc\MvcEvent | m\MockInterface $mockEvent */
        $mockEvent = m::mock(\Laminas\Mvc\MvcEvent::class);
        $mockEvent->shouldReceive('getRequest')->once()->withNoArgs()->andReturn($request);

        $this->sut->onDispatch($mockEvent);

        $this->assertEquals(
            false,
            $this->mockNavigation->findBy('id', $this->dashboardPermitsKey)->getVisible()
        );

        $this->assertEquals(
            false,
            $this->mockNavigation->findBy('id', $this->dashboardMessagingKey)->getVisible()
        );
    }

    /**
     * @dataProvider dpDispatchNoReferer
     */
    public function testOnDispatchWithNoReferal($eligibleForPermits)
    {
        $this->mockIdentity->shouldReceive('isAnonymous')->once()->withNoArgs()->andReturn(false);

        $this->mockIdentity->expects('getUserData')
            ->twice()
            ->andReturn([
                'eligibleForPermits' => $eligibleForPermits,
                'hasOrganisationSubmittedLicenceApplication' => false,
                'organisationUsers' => [
                    0 => [
                        'organisation' => [
                            'isMessagingDisabled' => false
                        ]
                    ]
                ]
            ]);

        $this->mockQuerySender->shouldReceive('featuresEnabled')->with([$this->messagingToggle])->once();

        $this->mockNavigation
            ->shouldReceive('findBy')
            ->with('id', $this->dashboardPermitsKey)
            ->twice()
            ->andReturn($this->dashboardPermitsPage);

        $this->mockNavigation
            ->shouldReceive('findBy')
            ->with('id', $this->dashboardMessagingKey)
            ->once()
            ->andReturn($this->dashboardMessagingPage);

        $request = m::mock(HttpRequest::class);
        $request->shouldReceive('getHeader')->once()->with('referer')->andReturn(false);

        /** @var \Laminas\Mvc\MvcEvent | m\MockInterface $mockEvent */
        $mockEvent = m::mock(\Laminas\Mvc\MvcEvent::class);
        $mockEvent->shouldReceive('getRequest')->once()->withNoArgs()->andReturn($request);

        $this->sut->onDispatch($mockEvent);

        $this->assertEquals(
            $eligibleForPermits,
            $this->mockNavigation->findBy('id', $this->dashboardPermitsKey)->getVisible()
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
        $uri = 'uri';
        $this->sut->setGovUkReferers([$uri]);

        //mock the http referer - this will be checked against our list of gov.uk referers (and will match)
        $referer = m::mock(HttpReferer::class);
        $referer->shouldReceive('getUri')->once()->withNoArgs()->andReturn($uri);
        $request = m::mock(HttpRequest::class);
        $request->shouldReceive('getHeader')->once()->with('referer')->andReturn($referer);

        $this->mockIdentity->expects('getUserData')
            ->once()
            ->andReturn([
                'hasOrganisationSubmittedLicenceApplication' => false,
                'organisationUsers' => [
                    0 => [
                        'organisation' => [
                            'isMessagingDisabled' => false
                        ]
                    ]
                ]
            ]);

        $this->mockQuerySender->shouldReceive('featuresEnabled')->once()->with([$this->messagingToggle]);

        $this->mockNavigation
            ->shouldReceive('findBy')
            ->twice()
            ->with('id', $this->dashboardPermitsKey)
            ->andReturn($this->dashboardPermitsPage);

        $this->mockNavigation
            ->shouldReceive('findBy')
            ->with('id', $this->dashboardMessagingKey)
            ->once()
            ->andReturn($this->dashboardMessagingPage);

        /** @var \Laminas\Mvc\MvcEvent | m\MockInterface $mockEvent */
        $mockEvent = m::mock(\Laminas\Mvc\MvcEvent::class);
        $mockEvent->shouldReceive('getRequest')->once()->withNoArgs()->andReturn($request);

        $this->sut->onDispatch($mockEvent);

        $this->assertTrue(
            $this->mockNavigation->findBy('id', $this->dashboardPermitsKey)->getVisible()
        );
    }

    /**
     * @dataProvider dpDispatchWithoutMatchedReferer
     */
    public function testOnDispatchWithNoGovUkReferal($eligibleForPermits)
    {
        $this->mockIdentity->shouldReceive('isAnonymous')
            ->andReturn(false);

        $this->mockIdentity->expects('getUserData')
            ->twice()
            ->andReturn([
                'eligibleForPermits' => $eligibleForPermits,
                'hasOrganisationSubmittedLicenceApplication' => false,
                'organisationUsers' => [
                    0 => [
                        'organisation' => [
                            'isMessagingDisabled' => false
                        ]
                    ]
                ]
            ]);

        $this->mockNavigation
            ->shouldReceive('findBy')
            ->twice()
            ->with('id', $this->dashboardPermitsKey)
            ->andReturn($this->dashboardPermitsPage);

        $this->mockNavigation
            ->shouldReceive('findBy')
            ->once()
            ->with('id', $this->dashboardMessagingKey)
            ->andReturn($this->dashboardMessagingPage);

        //mock the http referer - this will be checked against our list of gov.uk referers (and won't match)
        $referer = m::mock(HttpReferer::class);
        $referer->shouldReceive('getUri')->once()->withNoArgs()->andReturn('uri');
        $request = m::mock(HttpRequest::class);
        $request->shouldReceive('getHeader')->once()->with('referer')->andReturn($referer);

        /** @var \Laminas\Mvc\MvcEvent | m\MockInterface $mockEvent */
        $mockEvent = m::mock(\Laminas\Mvc\MvcEvent::class);
        $mockEvent->shouldReceive('getRequest')->once()->withNoArgs()->andReturn($request);

        $this->mockQuerySender->shouldReceive('featuresEnabled')->once()->with([$this->messagingToggle]);

        $this->sut->onDispatch($mockEvent);

        $this->assertEquals(
            $eligibleForPermits,
            $this->mockNavigation->findBy('id', $this->dashboardPermitsKey)->getVisible()
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
