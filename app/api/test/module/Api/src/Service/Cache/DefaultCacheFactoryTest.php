<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Cache;

use Dvsa\Olcs\Api\Service\Cache\DefaultCacheFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;

final class DefaultCacheFactoryTest extends MockeryTestCase
{
    public function testInvokeReturnsSymfonyRedisAdapter(): void
    {
        $redis = m::mock(\Redis::class);
        $container = m::mock(ContainerInterface::class);

        $config = [
            'caches' => [
                'default-cache' => [
                    'options' => [
                        'ttl' => 3600,
                        'namespace' => 'zfcache',
                    ],
                ],
            ],
        ];

        $container
            ->expects('get')
            ->with('Config')
            ->andReturn($config);

        $container
            ->expects('get')
            ->with('cache.redis.connection')
            ->andReturn($redis);

        $factory = new DefaultCacheFactory();

        $cache = $factory($container);

        self::assertInstanceOf(RedisAdapter::class, $cache);
        self::assertInstanceOf(CacheItemPoolInterface::class, $cache);
    }

    public function testInvokeUsesDefaultNamespaceAndTtlWhenNotConfigured(): void
    {
        $redis = m::mock(\Redis::class);
        $container = m::mock(ContainerInterface::class);

        $container
            ->expects('get')
            ->with('Config')
            ->andReturn([]);

        $container
            ->expects('get')
            ->with('cache.redis.connection')
            ->andReturn($redis);

        $factory = new DefaultCacheFactory();

        $cache = $factory($container);

        self::assertInstanceOf(RedisAdapter::class, $cache);
        self::assertInstanceOf(CacheItemPoolInterface::class, $cache);
    }
}