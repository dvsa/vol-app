<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Idp;

use Symfony\Component\Uid\UuidV7;

/**
 * UUIDv7: time-ordered, so it stays index-friendly as a BINARY(16) unique key, and
 * non-enumerable, so it is safe to expose on the bus and in S3 prefixes.
 */
class AnalysisTokenGenerator
{
    /** @return array{0: string, 1: string} raw 16 bytes for storage, string for the wire */
    public function generate(): array
    {
        $token = new UuidV7();

        return [$token->toBinary(), $token->toRfc4122()];
    }
}
