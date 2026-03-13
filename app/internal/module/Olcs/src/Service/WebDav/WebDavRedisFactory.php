<?php

declare(strict_types=1);

namespace Olcs\Service\WebDav;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

/**
 * Creates a dedicated Redis connection for WebDAV token caching and lock storage.
 *
 * A separate connection is required because the application's shared 'default-cache' Redis
 * connection uses SERIALIZER_IGBINARY, which auto-serializes values on write/read. WebDAV
 * code uses manual serialize()/unserialize() with allowed_classes restrictions for security,
 * so reusing that connection would cause double-serialization issues.
 */
class WebDavRedisFactory implements FactoryInterface
{
    public const SERVICE_NAME = 'WebDavRedis';

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null): ?\Redis
    {
        $config = $container->get('Config');
        $redisConfig = $config['caches']['default-cache']['options']['server'] ?? [];

        try {
            $redis = new \Redis();
            @$redis->connect(
                $redisConfig['host'] ?? 'localhost',
                (int) ($redisConfig['port'] ?? 6379),
                5.0
            );
            return $redis;
        } catch (\RedisException) {
            return null;
        }
    }
}
