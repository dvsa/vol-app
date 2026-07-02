<?php

namespace CommonTest\View\Helper;

use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\View\Helper\PageId;
use Laminas\Router\Http\RouteMatch;

/**
 * Page Id Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PageIdTest extends MockeryTestCase
{
    /**
     * @var PageId
     */
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        $action = 'someaction';
        $routeMatchName = 'foo/bar';
        $routeMatch = m::mock(RouteMatch::class);
        $routeMatch->shouldReceive('getMatchedRouteName')->andReturn($routeMatchName);
        $routeMatch->shouldReceive('getParam')->with('action')->andReturn($action);

        $this->sut = new PageId($routeMatchName, $action);
    }

    public function testInvoke(): void
    {
        $routeMatch = m::mock(RouteMatch::class);
        $routeMatch->shouldReceive('getMatchedRouteName')->andReturn('foo/bar');
        $routeMatch->shouldReceive('getParam')->with('action')->andReturn('someaction');

        $sm = m::mock(ContainerInterface::class);
        $sm->shouldReceive('get->getMvcEvent->getRouteMatch')
            ->andReturn($routeMatch);

        $this->assertEquals('pg:foo/bar:someaction', $this->sut->__invoke());
    }
}
