<?php

namespace OlcsTest\Listener;

use Common\FeatureToggle;
use Common\Service\Cqrs\Query\QuerySender;
use Olcs\Controller\Listener\Navigation as NavigationListener;
use Mockery as m;
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
    public function testOnDispatch($dashboardPermitsEnabled)
    {
        $dashboardPermitsKey = 'dashboard-permits';
        $dashboardPermitsPage = new Uri();

        $this->mockQuerySender->shouldReceive('featuresEnabled')
            ->with([FeatureToggle::SELFSERVE_ECMT])
            ->andReturn($dashboardPermitsEnabled);

        $this->mockNavigation
            ->shouldReceive('findBy')
            ->with('id', $dashboardPermitsKey)
            ->andReturn($dashboardPermitsPage);

        /** @var \Zend\Mvc\MvcEvent | m\MockInterface $mockEvent */
        $mockEvent = m::mock(\Zend\Mvc\MvcEvent::class);
        $this->sut->onDispatch($mockEvent);

        $this->assertEquals(
            $dashboardPermitsEnabled,
            $this->mockNavigation->findBy('id', $dashboardPermitsKey)->getVisible()
        );
    }

    public function dpDispatch(): array
    {
        return [
            [true],
            [false]
        ];
    }
}
