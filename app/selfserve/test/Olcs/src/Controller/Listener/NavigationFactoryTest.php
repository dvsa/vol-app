<?php

namespace OlcsTest\Controller\Listener;

use Common\Rbac\User as RbacUser;
use Common\Service\Cqrs\Query\QuerySender;
use Mockery as m;
use Olcs\Controller\Listener\Navigation as NavigationListener;
use Olcs\Controller\Listener\NavigationFactory;
use Zend\Navigation\Navigation as ZendNavigation;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfcRbac\Service\AuthorizationService;

/**
 * Class NavigationFactoryTest
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class NavigationFactoryTest extends m\Adapter\Phpunit\MockeryTestCase
{
    public function testCreateService()
    {
        $identity = m::mock(RbacUser::class);
        $navigation = m::mock(ZendNavigation::class);
        $querySender = m::mock(QuerySender::class);
        $authService = m::mock(AuthorizationService::class);
        $authService->shouldReceive('getIdentity')->once()->withNoArgs()->andReturn($identity);

        $mockSl = m::mock(ServiceLocatorInterface::class);
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
        $toggleService = $sut->createService($mockSl);

        $this->assertInstanceOf(NavigationListener::class, $toggleService);
    }
}
