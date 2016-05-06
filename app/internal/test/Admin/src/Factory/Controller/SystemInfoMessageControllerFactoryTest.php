<?php

namespace AdminTest\Factory\Controller;

use Admin\Controller\SystemInfoMessageController;
use Admin\Factory\Controller\SystemInfoMessageControllerFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\ServiceManager\ServiceManagerAwareInterface;

/**
 * @covers Admin\Factory\Controller\SystemInfoMessageControllerFactory
 */
class SystemInfoMessageControllerFactoryTest extends MockeryTestCase
{
    public function test()
    {
        $sm = m::mock(\Zend\ServiceManager\ServiceManager::class);
        $sm->shouldReceive('get')
            ->with('Helper\Form')
            ->andReturn(m::mock(\Common\Service\Helper\FormHelperService::class));

        /** @var ServiceLocatorInterface|m\MockInterface $sl */
        $sl = m::mock(ServiceLocatorInterface::class);
        $sl->shouldReceive('getServiceLocator')
            ->andReturn($sm);

        static::assertInstanceOf(
            SystemInfoMessageController::class,
            (new SystemInfoMessageControllerFactory())->createService($sl)
        );
    }
}
