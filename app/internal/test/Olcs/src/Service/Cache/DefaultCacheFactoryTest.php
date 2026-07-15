<?php

declare(strict_types=1);

namespace OlcsTest\Service\Cache;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Cache\DefaultCacheFactory;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;

final class DefaultCacheFactoryTest extends MockeryTestCase
{
    public function testInvokeReturnsSymfonyRedisAdapter(): void
    {
        $redis = m::mock(\Redis::class);
        $container = m::mock(ContainerInterface::class);

        $container
            ->expects('get')
            ->with('Config')
            ->andReturn([
                'caches' => [
                    'default-cache' => [
                        'options' => [
                            'ttl' => 3600,
                            'namespace' => 'zfcache',
                        ],
                    ],
                ],
            ]);

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