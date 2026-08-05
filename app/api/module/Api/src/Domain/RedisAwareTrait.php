<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain;

trait RedisAwareTrait
{
    private \Redis $redis;

    public function setRedis(\Redis $redis): void
    {
        $this->redis = $redis;
    }

    protected function getRedis(): \Redis
    {
        return $this->redis;
    }
}
