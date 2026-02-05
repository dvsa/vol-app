<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Listener;

use Common\Rbac\User as RbacUser;
use Common\Service\Cqrs\Query\QuerySender;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Olcs\Controller\Listener\Navigation as NavigationListener;
use Olcs\Controller\Listener\NavigationFactory;
use Laminas\Navigation\Navigation as LaminasNavigation;
use LmcRbacMvc\Service\AuthorizationService;

class NavigationFactoryTest extends m\Adapter\Phpunit\MockeryTestCase
{
    public function testInvoke(): void
    {
        $navigation = m::mock(LaminasNavigation::class);
        $querySender = m::mock(QuerySender::class);
        $authService = m::mock(AuthorizationService::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')
            ->once()
            ->with('navigation')
            ->andReturn($navigation);
        $mockSl->shouldReceive('get')
            ->once()
            ->with('QuerySender')
            ->andReturn($querySender);
        $mockSl->shouldReceive('get')
            ->once()
            ->with(AuthorizationService::class)
            ->andReturn($authService);

        $sut = new NavigationFactory();
        $navigationListener = $sut->__invoke($mockSl, NavigationListener::class);

        $this->assertInstanceOf(NavigationListener::class, $navigationListener);
    }
}
