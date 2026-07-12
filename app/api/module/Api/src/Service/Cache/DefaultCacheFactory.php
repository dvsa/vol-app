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

        $host = $options['server']['host'] ?? 'redis';
        $port = $options['server']['port'] ?? 6379;
        $namespace = $options['namespace'] ?? 'zfcache';
        $ttl = $options['ttl'] ?? 3600;

        $redis = RedisAdapter::createConnection(sprintf('redis://%s:%d', $host, $port));

        return new RedisAdapter($redis, $namespace, $ttl);
    }
}