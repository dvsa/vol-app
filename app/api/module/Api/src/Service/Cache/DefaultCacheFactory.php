<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;

class DefaultCacheFactory
{
    public function __invoke(ContainerInterface $container): CacheItemPoolInterface
    {
        $config = $container->get('Config');
        $options = $config['caches']['default-cache']['options'] ?? [];

        $namespace = $options['namespace'] ?? 'zfcache';
        $ttl = $options['ttl'] ?? 3600;

        $redis = $container->get('cache.redis.connection');

        if (!$redis instanceof \Redis) {
            throw new \RuntimeException('Redis connection service is invalid');
        }

        return new RedisAdapter($redis, $namespace, $ttl);
    }
}
