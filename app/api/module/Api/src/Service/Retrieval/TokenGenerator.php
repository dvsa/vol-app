<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Retrieval;

/**
 * Generates the opaque, unguessable identifiers used in Retrieve-via-Link URLs.
 *
 * These are the ONLY identifiers that ever appear in a link or a download url — real
 * document ids and internal primary keys are never exposed. A token carries no data; it is
 * a random lookup key resolved server-side, which keeps links revocable and non-enumerable.
 */
final class TokenGenerator
{
    /** 32 bytes = 256 bits of entropy; base64url-encodes to 43 characters. */
    public const DEFAULT_BYTES = 32;

    /** Refuse to mint anything weaker than 128 bits. */
    public const MIN_BYTES = 16;

    /**
     * @throws \InvalidArgumentException when asked for fewer than MIN_BYTES of entropy
     */
    public function generate(int $bytes = self::DEFAULT_BYTES): string
    {
        if ($bytes < self::MIN_BYTES) {
            throw new \InvalidArgumentException(
                sprintf('Refusing to generate a token with fewer than %d bytes of entropy', self::MIN_BYTES),
            );
        }

        return self::base64Url(random_bytes($bytes));
    }

    /**
     * URL-safe, unpadded base64 (RFC 4648 §5) so the value drops straight into a path segment.
     */
    private static function base64Url(string $raw): string
    {
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }
}
