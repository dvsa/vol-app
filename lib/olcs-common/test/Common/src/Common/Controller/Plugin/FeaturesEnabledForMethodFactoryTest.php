<?php

namespace CommonTest\Controller\Plugin;

use Common\Controller\Plugin\FeaturesEnabledForMethod;
use Common\Controller\Plugin\FeaturesEnabledForMethodFactory;
use Common\Service\Cqrs\Query\QuerySender;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

class FeaturesEnabledForMethodFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $mockQuerySender = m::mock(QuerySender::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('QuerySender')->andReturn($mockQuerySender);
        $sut = new FeaturesEnabledForMethodFactory();
        $service = $sut->__invoke($mockSl, FeaturesEnabledForMethod::class);

        $this->assertInstanceOf(FeaturesEnabledForMethod::class, $service);
    }
}
