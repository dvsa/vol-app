<?php

namespace OlcsTest\Listener;

use Common\Rbac\User as RbacUser;
use Common\Service\Cqrs\Query\QuerySender;
use Laminas\Navigation\Page\AbstractPage;
use Olcs\Controller\Listener\Navigation as NavigationListener;
use Mockery as m;
use Laminas\Http\Header\Referer as HttpReferer;
use Laminas\Http\PhpEnvironment\Request as HttpRequest;
use Laminas\Navigation\Navigation;
use Laminas\Navigation\Page\Uri;
use Laminas\Mvc\MvcEvent;
use Common\Service\Cqrs\Response as CqrsResponse;

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
        $this->mockResponse = m::mock(CqrsResponse::class);
        $this->mockIdentity = m::mock(RbacUser::class);
        $this->sut = new NavigationListener($this->mockNavigation, $this->mockQuerySender, $this->mockIdentity);

        $this->dashboardPermitsKey = 'dashboard-permits';
        $this->dashboardPermitsPage = new Uri();

        $this->dashboardMessagingKey = 'dashboard-messaging';
        $this->dashboardMessagingPage = new Uri();

        $this->dashboardMenuKey = 'dashboard-licences-applications';
        $this->mockDashboardMenu = m::mock(AbstractPage::class);

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
            ->twice()
            ->andReturn([
                'hasOrganisationSubmittedLicenceApplication' => false,
                'organisationUsers' => [
                    0 => [
                        'organisation' => [
                            'isMessagingDisabled' => false,
                            'id' => 1,
                        ]
                    ]
                ]
            ]);

        $this->mockQuerySender->shouldReceive('featuresEnabled')->with([$this->messagingToggle])->once();

        $this->mockNavigation
            ->shouldReceive('findBy')
            ->twice()
            ->with('id', $this->dashboardPermitsKey)
            ->andReturn($this->dashboardPermitsPage);

        $this->mockNavigation
            ->shouldReceive('findBy')
            ->twice()
            ->with('id', $this->dashboardMessagingKey)
            ->andReturn($this->dashboardMessagingPage);

        $this->mockNavigation
            ->shouldReceive('findBy')
            ->with('id', $this->dashboardMenuKey)
            ->once()
            ->andReturn($this->mockDashboardMenu);

        $this->mockDashboardMenu
            ->shouldReceive('findBy')
            ->with('id', $this->dashboardMessagingKey)
            ->once()
            ->andReturn($this->dashboardMessagingPage);

        $this->mockIdentity->shouldReceive('getId')
            ->once()
            ->andReturn(1);

        $this->mockQuerySender->shouldReceive('send')
            ->once()
            ->andReturn($this->mockResponse);

        $this->mockResponse->shouldReceive('getResult')
            ->once();

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
            ->times(3)
            ->andReturn([
                'eligibleForPermits' => $eligibleForPermits,
                'hasOrganisationSubmittedLicenceApplication' => false,
                'organisationUsers' => [
                    0 => [
                        'organisation' => [
                            'isMessagingDisabled' => false,
                            'id' => 1,
                        ]
                    ]
                ]
            ]);

        $this->mockQuerySender->shouldReceive('featuresEnabled')->with([$this->messagingToggle])->once();

        $this->navigationExpectations();

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
            ->twice()
            ->andReturn([
                'hasOrganisationSubmittedLicenceApplication' => false,
                'organisationUsers' => [
                    0 => [
                        'organisation' => [
                            'isMessagingDisabled' => false,
                            'id' => 1,
                        ]
                    ]
                ]
            ]);

        $this->mockQuerySender->shouldReceive('featuresEnabled')->once()->with([$this->messagingToggle]);

        $this->navigationExpectations();

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
            ->times(3)
            ->andReturn([
                'eligibleForPermits' => $eligibleForPermits,
                'hasOrganisationSubmittedLicenceApplication' => false,
                'organisationUsers' => [
                    0 => [
                        'organisation' => [
                            'isMessagingDisabled' => false,
                            'id' => 1,
                        ]
                    ]
                ]
            ]);

        $this->navigationExpectations();

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

    public function navigationExpectations(){
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

        $this->mockNavigation
            ->shouldReceive('findBy')
            ->with('id', $this->dashboardMenuKey)
            ->once()
            ->andReturn($this->mockDashboardMenu);

        $this->mockDashboardMenu
            ->shouldReceive('findBy')
            ->with('id', $this->dashboardMessagingKey)
            ->once()
            ->andReturn($this->dashboardMessagingPage);

        $this->mockIdentity->shouldReceive('getId')
            ->once()
            ->andReturn(1);

        $this->mockQuerySender->shouldReceive('send')
            ->once()
            ->andReturn($this->mockResponse);

        $this->mockResponse->shouldReceive('getResult')
            ->once();
    }

}
