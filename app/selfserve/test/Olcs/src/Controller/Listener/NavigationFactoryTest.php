<?php

namespace OlcsTest\Controller\Listener;

use Common\Service\Cqrs\Query\QuerySender;
use Mockery as m;
use Olcs\Controller\Listener\Navigation as NavigationListener;
use Olcs\Controller\Listener\NavigationFactory;
use Zend\Navigation\Navigation as ZendNavigation;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Class NavigationFactoryTest
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class NavigationFactoryTest extends m\Adapter\Phpunit\MockeryTestCase
{
    public function testCreateService()
    {
        $navigation = m::mock(ZendNavigation::class);
        $querySender = m::mock(QuerySender::class);

        $mockSl = m::mock(ServiceLocatorInterface::class);
        $mockSl->shouldReceive('get')
            ->once()
            ->with('navigation')
            ->andReturn($navigation);
        $mockSl->shouldReceive('get')
            ->once()
            ->with('QuerySender')
            ->andReturn($querySender);

        $sut = new NavigationFactory();
        $toggleService = $sut->createService($mockSl);

        $this->assertInstanceOf(NavigationListener::class, $toggleService);
    }
}
