<?php

namespace CommonTest\Service\User;

use Common\Service\Cqrs\Command\CommandSender;
use Common\Service\User\LastLoginService;
use Common\Service\User\LastLoginServiceFactory;
use Psr\Container\ContainerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

class LastLoginServiceFactoryTest extends MockeryTestCase
{
    public function testInvoke(): void
    {
        $mockCommandSender = m::mock(CommandSender::class);

        $mockServiceLocator = m::mock(ContainerInterface::class);
        $mockServiceLocator->shouldReceive('get')->with('CommandSender')->andReturn($mockCommandSender);

        $sut = new LastLoginServiceFactory();
        $instance = $sut->__invoke($mockServiceLocator, LastLoginService::class);

        $this->assertInstanceOf(LastLoginService::class, $instance);
    }
}
