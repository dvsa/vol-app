<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Framework\Support;

/**
 * A query result that is neither an array nor a QueryHandler\Result, which the Response controller plugin
 * still accepts through jsonSerialize() (and logs as legacy)
 */
final class LegacyJsonResult implements \JsonSerializable
{
    #[\Override]
    public function jsonSerialize(): array
    {
        return ['legacy' => true];
    }
}
