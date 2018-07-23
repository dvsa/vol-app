<?php

namespace OlcsTest\Listener;

use Common\FeatureToggle;
use Common\Rbac\User as RbacUser;
use Common\Service\Cqrs\Query\QuerySender;
use Dvsa\Olcs\Transfer\Query\Organisation\EligibleForPermits;
use Olcs\Controller\Listener\Navigation as NavigationListener;
use Mockery as m;
use Zend\Http\Header\Referer as HttpReferer;
use Zend\Http\Response as HttpResponse;
use Zend\Http\PhpEnvironment\Request as HttpRequest;
use Zend\Navigation\Navigation;
use Zend\Navigation\Page\Uri;
use Zend\Mvc\MvcEvent;

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

    public function setUp()
    {
        $this->mockNavigation = m::mock(Navigation::class);
        $this->mockQuerySender = m::mock(QuerySender::class);
        $this->mockIdentity = m::mock(RbacUser::class);
        $this->sut = new NavigationListener($this->mockNavigation, $this->mockQuerySender, $this->mockIdentity);
    }

    public function testAttach()
    {
        /** @var \Zend\EventManager\EventManagerInterface | m\MockInterface $mockEventManager */
        $mockEventManager = m::mock(\Zend\EventManager\EventManagerInterface::class);
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

        /** @var \Zend\Mvc\MvcEvent | m\MockInterface $mockEvent */
        $mockEvent = m::mock(\Zend\Mvc\MvcEvent::class);
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
    public function testOnDispatchWithNoReferal($ecmtEnabled, $eligibleForPermits, $tabDisplayed)
    {
        $dashboardPermitsKey = 'dashboard-permits';
        $dashboardPermitsPage = new Uri();

        $httpResponse = m::mock(HttpResponse::class);

        $this->mockIdentity->shouldReceive('isAnonymous')->once()->withNoArgs()->andReturn(false);

        $httpResponse->shouldReceive('getResult')
            ->withNoArgs()
            ->once()
            ->andReturn(['eligibleForPermits' => $eligibleForPermits]);

        $this->mockQuerySender->shouldReceive('send')
            ->with(m::type(EligibleForPermits::class))
            ->once()
            ->andReturn($httpResponse);

        //only need to check feature is enabled if the user is eligible in the first place
        $this->mockQuerySender->shouldReceive('featuresEnabled')
            ->with([FeatureToggle::SELFSERVE_ECMT])
            ->times($eligibleForPermits ? 1 : 0)
            ->andReturn($ecmtEnabled);

        $this->mockNavigation
            ->shouldReceive('findBy')
            ->with('id', $dashboardPermitsKey)
            ->twice()
            ->andReturn($dashboardPermitsPage);

        $request = m::mock(HttpRequest::class);
        $request->shouldReceive('getHeader')->once()->with('referer')->andReturn(false);

        /** @var \Zend\Mvc\MvcEvent | m\MockInterface $mockEvent */
        $mockEvent = m::mock(\Zend\Mvc\MvcEvent::class);
        $mockEvent->shouldReceive('getRequest')->once()->withNoArgs()->andReturn($request);

        $this->sut->onDispatch($mockEvent);

        $this->assertEquals(
            $tabDisplayed,
            $this->mockNavigation->findBy('id', $dashboardPermitsKey)->getVisible()
        );
    }

    public function dpDispatchNoReferer()
    {
        return [
            [true, true, true],
            [false, false, false],
            [true, false, false],
            [false, true, false]
        ];
    }

    /**
     * @dataProvider dpDispatchWithMatchedReferer
     */
    public function testOnDispatchWithGovUkReferalMatch($ecmtEnabled, $tabDisplayed)
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

        $this->mockQuerySender->shouldReceive('featuresEnabled')
            ->once()
            ->with([FeatureToggle::SELFSERVE_ECMT])
            ->andReturn($ecmtEnabled);

        $this->mockNavigation
            ->shouldReceive('findBy')
            ->twice()
            ->with('id', $dashboardPermitsKey)
            ->andReturn($dashboardPermitsPage);

        /** @var \Zend\Mvc\MvcEvent | m\MockInterface $mockEvent */
        $mockEvent = m::mock(\Zend\Mvc\MvcEvent::class);
        $mockEvent->shouldReceive('getRequest')->once()->withNoArgs()->andReturn($request);

        $this->sut->onDispatch($mockEvent);

        $this->assertEquals(
            $tabDisplayed,
            $this->mockNavigation->findBy('id', $dashboardPermitsKey)->getVisible()
        );
    }

    public function dpDispatchWithMatchedReferer()
    {
        return [
            [true, true],
            [false, false],
        ];
    }

    /**
     * @dataProvider dpDispatchWithoutMatchedReferer
     */
    public function testOnDispatchWithNoGovUkReferal($ecmtEnabled, $eligibleForPermits, $tabDisplayed)
    {
        $dashboardPermitsKey = 'dashboard-permits';
        $dashboardPermitsPage = new Uri();

        $httpResponse = m::mock(HttpResponse::class);

        $this->mockIdentity->shouldReceive('isAnonymous')
            ->once()
            ->withNoArgs()
            ->andReturn(false);

        $httpResponse->shouldReceive('getResult')
            ->withNoArgs()
            ->once()
            ->andReturn(['eligibleForPermits' => $eligibleForPermits]);

        $this->mockQuerySender->shouldReceive('send')
            ->with(m::type(EligibleForPermits::class))
            ->once()
            ->andReturn($httpResponse);

        $this->mockQuerySender->shouldReceive('featuresEnabled')
            ->times($eligibleForPermits ? 1 : 0)
            ->with([FeatureToggle::SELFSERVE_ECMT])
            ->andReturn($ecmtEnabled);

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

        /** @var \Zend\Mvc\MvcEvent | m\MockInterface $mockEvent */
        $mockEvent = m::mock(\Zend\Mvc\MvcEvent::class);
        $mockEvent->shouldReceive('getRequest')->once()->withNoArgs()->andReturn($request);

        $this->sut->onDispatch($mockEvent);

        $this->assertEquals(
            $tabDisplayed,
            $this->mockNavigation->findBy('id', $dashboardPermitsKey)->getVisible()
        );
    }

    public function dpDispatchWithoutMatchedReferer()
    {
        return [
            [true, true, true],
            [false, false, false],
            [true, false, false],
            [false, true, false]
        ];
    }
}
