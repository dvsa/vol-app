<?php

declare(strict_types=1);

namespace OlcsTest\Listener;

use Common\Rbac\User as RbacUser;
use Common\Service\Cqrs\Query\QuerySender;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Navigation\Page\AbstractPage;
use LmcRbacMvc\Service\AuthorizationService;
use Olcs\Controller\Listener\Navigation as NavigationListener;
use Mockery as m;
use Common\RefData;
use Laminas\Http\Header\Referer as HttpReferer;
use Laminas\Http\PhpEnvironment\Request as HttpRequest;
use Laminas\Navigation\Navigation;
use Laminas\Navigation\Page\Uri;
use Laminas\Mvc\MvcEvent;
use Common\Service\Cqrs\Response as CqrsResponse;

class NavigationTest extends m\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @var (\Common\Service\Cqrs\Response & \Mockery\MockInterface)
     */
    public $mockResponse;
    public $mockAuthService;
    public $dashboardPermitsKey;
    public $dashboardPermitsPage;
    public $dashboardMessagingKey;
    public $dashboardMessagingPage;
    /**
     * @var string
     */
    public $dashboardMenuKey;
    /**
     * @var (\Laminas\Navigation\Page\AbstractPage & \Mockery\MockInterface)
     */
    public $mockDashboardMenu;
    public $messagingToggle;
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
        $this->mockAuthService = m::mock(AuthorizationService::class);

        $this->sut = new NavigationListener($this->mockNavigation, $this->mockQuerySender, $this->mockAuthService);

        $this->dashboardPermitsKey = 'dashboard-permits';
        $this->dashboardPermitsPage = new Uri();

        $this->dashboardMessagingKey = 'dashboard-messaging';
        $this->dashboardMessagingPage = new Uri();

        $this->dashboardMenuKey = 'dashboard-licences-applications';
        $this->mockDashboardMenu = m::mock(AbstractPage::class);

        $this->messagingToggle = 'messaging';
    }

    public function testAttach(): void
    {
        /** @var EventManagerInterface | m\MockInterface $mockEventManager */
        $mockEventManager = m::mock(EventManagerInterface::class);
        $mockEventManager->expects('attach')
            ->with(
                MvcEvent::EVENT_DISPATCH,
                m::on(function ($listener) {
                    $rf = new \ReflectionFunction($listener);
                    return $rf->getClosureThis() === $this->sut && $rf->getName() === 'onDispatch';
                }),
                20
            );

        $this->sut->attach($mockEventManager);
    }

    public function testOnDispatchWithNoReferalAnonymousUser(): void
    {
        $this->mockAuthService->shouldReceive('getIdentity->isAnonymous')->once()->withNoArgs()->andReturn(true);

        $this->mockAuthService->expects('getIdentity->getUserData')
            ->once()
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
            ->once()
            ->with('id', $this->dashboardMessagingKey)
            ->andReturn($this->dashboardMessagingPage);

        $this->mockAuthService->shouldReceive('isGranted')
            ->with(RefData::PERMISSION_CAN_LIST_CONVERSATIONS)
            ->once()
            ->andReturn(1);

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
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpDispatchNoReferer')]
    public function testOnDispatchWithNoReferal(bool $eligibleForPermits): void
    {
        $this->mockAuthService->shouldReceive('getIdentity->isAnonymous')->once()->withNoArgs()->andReturn(false);

        $this->mockAuthService->expects('getIdentity->getUserData')
            ->times(2)
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

    /**
     * @return bool[][]
     *
     * @psalm-return list{list{true}, list{false}}
     */
    public static function dpDispatchNoReferer(): array
    {
        return [
            [true],
            [false],
        ];
    }

    public function testOnDispatchWithGovUkReferalMatch(): void
    {
        $uri = 'uri';
        $this->sut->setGovUkReferers([$uri]);

        //mock the http referer - this will be checked against our list of gov.uk referers (and will match)
        $referer = m::mock(HttpReferer::class);
        $referer->shouldReceive('getUri')->once()->withNoArgs()->andReturn($uri);
        $request = m::mock(HttpRequest::class);
        $request->shouldReceive('getHeader')->once()->with('referer')->andReturn($referer);

        $this->mockAuthService->expects('getIdentity->getUserData')
            ->once()
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpDispatchWithoutMatchedReferer')]
    public function testOnDispatchWithNoGovUkReferal(bool $eligibleForPermits): void
    {
        $this->mockAuthService->shouldReceive('getIdentity->isAnonymous')
            ->andReturn(false);

        $this->mockAuthService->expects('getIdentity->getUserData')
            ->times(2)
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

    /**
     * @return bool[][]
     *
     * @psalm-return list{list{true}, list{false}}
     */
    public static function dpDispatchWithoutMatchedReferer(): array
    {
        return [
            [true],
            [false],
        ];
    }

    public function navigationExpectations(): void
    {
        $this->mockAuthService->shouldReceive('isGranted')
            ->with(RefData::PERMISSION_CAN_LIST_CONVERSATIONS)
            ->once()
            ->andReturn(1);

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
    }
}
