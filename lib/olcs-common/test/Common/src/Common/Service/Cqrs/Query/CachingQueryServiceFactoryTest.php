<?php

namespace CommonTest\Common\Service\Cqrs\Query;

use Common\Service\Cqrs\Query\CachingQueryService;
use Common\Service\Cqrs\Query\CachingQueryServiceFactory;
use Common\Service\Cqrs\Query\QueryService;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class CachingQueryServiceFactoryTest extends MockeryTestCase
{
    public function testInvoke(): void
    {
        $config = [
            'query_cache' => [
                'enabled' => true,
                'ttl' => [
                    'query type' => 300
                ],
            ],
        ];

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('Config')->andReturn($config);
        $mockSl->shouldReceive('get')->with('Logger')->andReturn(m::mock(\Psr\Log\LoggerInterface::class));
        $mockSl->shouldReceive('get')->with('TransferAnnotationBuilder')->andReturn(m::mock(AnnotationBuilder::class));
        $mockSl->shouldReceive('get')->with(QueryService::class)->andReturn(m::mock(QueryService::class));
        $mockSl->shouldReceive('get')->with(CacheEncryption::class)->andReturn(m::mock(CacheEncryption::class));

        $sut = new CachingQueryServiceFactory();
        $service = $sut->__invoke($mockSl, CachingQueryService::class);

        $this->assertInstanceOf(CachingQueryService::class, $service);
    }

    public function testInvokeMissingQueryCacheConfig(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Query cache config key missing');

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('Config')->andReturn([]);

        $sut = new CachingQueryServiceFactory();
        $sut->__invoke($mockSl, CachingQueryService::class);
    }

    public function testInvokeMissingQueryCacheEnabledConfig(): void
    {
        $config = [
            'query_cache' => [
                'ttl' => [
                    'query type' => 300
                ],
            ],
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Query cache enabled/disabled config key missing');

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('Config')->andReturn($config);

        $sut = new CachingQueryServiceFactory();
        $sut->__invoke($mockSl, CachingQueryService::class);
    }
}
