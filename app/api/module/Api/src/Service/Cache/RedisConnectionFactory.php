<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Cache;

use Psr\Container\ContainerInterface;
use Symfony\Component\Cache\Adapter\RedisAdapter;

final class RedisConnectionFactory
{
    public function __invoke(ContainerInterface $container): \Redis
    {
        $config = $container->get('Config');
        $options = $config['caches']['default-cache']['options'] ?? [];

        $host = $options['server']['host'] ?? 'redis';
        $port = $options['server']['port'] ?? 6379;

        /** @psalm-suppress UndefinedDocblockClass */
        $connection = RedisAdapter::createConnection(
            sprintf('redis://%s:%d', $host, $port)
        );

        if (!$connection instanceof \Redis) {
            throw new \RuntimeException('Could not create Redis connection');
        }

        return $connection;
    }
}
