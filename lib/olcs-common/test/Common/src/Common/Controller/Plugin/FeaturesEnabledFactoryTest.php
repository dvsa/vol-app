<?php

namespace CommonTest\Controller\Plugin;

use Common\Controller\Plugin\FeaturesEnabled;
use Common\Controller\Plugin\FeaturesEnabledFactory;
use Common\Service\Cqrs\Query\QuerySender;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

class FeaturesEnabledFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $mockQuerySender = m::mock(QuerySender::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('QuerySender')->andReturn($mockQuerySender);
        $sut = new FeaturesEnabledFactory();
        $service = $sut->__invoke($mockSl, FeaturesEnabled::class);

        $this->assertInstanceOf(FeaturesEnabled::class, $service);
    }
}
