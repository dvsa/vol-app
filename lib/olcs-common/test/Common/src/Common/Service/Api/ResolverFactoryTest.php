<?php

namespace CommonTest\Service\Api;

use Common\Service\Api\Resolver;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Service\Api\ResolverFactory;
use Mockery as m;
use Psr\Container\ContainerInterface;

class ResolverFactoryTest extends MockeryTestCase
{
    public function testInvoke(): void
    {
        $config = [
            'rest_services' => [
                'services' => [
                    'test' => 'testService'
                ]
            ]
        ];

        $container = m::mock(ContainerInterface::class);
        $container->expects('get')->with('Config')->andReturn($config);

        $sut = new ResolverFactory();
        $instance = $sut->__invoke($container, Resolver::class);

        $this->assertInstanceOf(Resolver::class, $instance);
        $this->assertEquals('testService', $instance->get('test'));
    }
}
