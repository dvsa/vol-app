<?php

declare(strict_types=1);

namespace Olcs\Service\GovUkAccount;

use Psr\Container\ContainerInterface;

final class CallbackReplayStoreFactory
{
    public function __invoke(ContainerInterface $container): CallbackReplayStore
    {
        $redis = $container->get('cache.redis.connection');

        if (!$redis instanceof \Redis) {
            throw new \RuntimeException('Redis connection service is invalid');
        }

        return new CallbackReplayStore($redis);
    }
}
