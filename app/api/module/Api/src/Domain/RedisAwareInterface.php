<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain;

interface RedisAwareInterface
{
    public function setRedis(\Redis $redis): void;
}
