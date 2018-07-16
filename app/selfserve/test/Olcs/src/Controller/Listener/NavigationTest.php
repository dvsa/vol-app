<?php

namespace OlcsTest\Listener;

use Common\FeatureToggle;
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

    public function setUp()
    {
        $this->mockNavigation = m::mock(Navigation::class);
        $this->mockQuerySender = m::mock(QuerySender::class);
        $this->sut = new NavigationListener($this->mockNavigation, $this->mockQuerySender);
    }

    public function testAttach()
    {
        /** @var \Zend\EventManager\EventManagerInterface | m\MockInterface $mockEventManager */
        $mockEventManager = m::mock(\Zend\EventManager\EventManagerInterface::class);
        $mockEventManager->shouldReceive('attach')->once()
            ->with(MvcEvent::EVENT_DISPATCH, [$this->sut, 'onDispatch'], 20);

        $this->sut->attach($mockEventManager);
    }

    /**
     * @dataProvider dpDispatch
     */
    public function testOnDispatchWithGovUkReferal($dashboardPermitsEnabled, $govUkReferer, $eligibleForPermits)
    {

        $dashboardPermitsKey = 'dashboard-permits';
        $dashboardPermitsPage = new Uri();

        $httpResponse = m::mock(HttpResponse::class);

        $httpResponse->shouldReceive('getResult')
            ->withNoArgs()
            ->times(empty($govUkReferer) ? 1 : 0)
            ->andReturn(['eligibleForPermits' => $eligibleForPermits]);

        $this->mockQuerySender->shouldReceive('send')
            ->with(m::type(EligibleForPermits::class))
            ->times(empty($govUkReferer) ? 1 : 0)
            ->andReturn($httpResponse);

        $this->mockQuerySender->shouldReceive('featuresEnabled')
            ->with([FeatureToggle::SELFSERVE_ECMT])
            ->andReturn($dashboardPermitsEnabled);

        $this->mockNavigation
            ->shouldReceive('findBy')
            ->with('id', $dashboardPermitsKey)
            ->andReturn($dashboardPermitsPage);

        $refererUri = 'uri';
        $this->sut->setGovUkReferers($govUkReferer);
        $referer = m::mock(HttpReferer::class);
        $referer->shouldReceive('getUri')->once()->withNoArgs()->andReturn($refererUri);

        $request = m::mock(HttpRequest::class);
        $request->shouldReceive('getHeader')->once()->with('referer')->andReturn($referer);

        /** @var \Zend\Mvc\MvcEvent | m\MockInterface $mockEvent */
        $mockEvent = m::mock(\Zend\Mvc\MvcEvent::class);
        $mockEvent->shouldReceive('getRequest')->once()->withNoArgs()->andReturn($request);

        $this->sut->onDispatch($mockEvent);

        $this->assertEquals(
            $dashboardPermitsEnabled,
            $this->mockNavigation->findBy('id', $dashboardPermitsKey)->getVisible()
        );
    }

    public function dpDispatch(): array
    {
        $govUkReferer = ['uri'];

        return [
            [true, $govUkReferer, true],
            [true, $govUkReferer, false],
            [true, [], true],
            [false, [], false]
        ];
    }
}
