<?php

namespace CommonTest\Common\Rbac\Role;

use Common\Rbac\Role\RoleProviderFactory;
use Common\Service\Cqrs\Query\QuerySender;
use Psr\Container\ContainerInterface;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use LmcRbacMvc\Role\RoleProviderInterface;

class RoleProviderFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $mockQuerySender = m::mock(QuerySender::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('getServiceLocator')->andReturnSelf();
        $mockSl->shouldReceive('get')->with('QuerySender')->andReturn($mockQuerySender);

        $sut = new RoleProviderFactory();
        $service = $sut->__invoke($mockSl, RoleProviderInterface::class);

        $this->assertInstanceOf(RoleProviderInterface::class, $service);
    }
}
