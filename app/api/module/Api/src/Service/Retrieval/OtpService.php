<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Retrieval;

/**
 * One-time-code generation and verification for OTP-gated retrieval links.
 *
 * The plaintext code is emailed to the original recipient and NEVER stored — only its hash is
 * persisted (see `retrieval_otp.code_hash`). Verification is delegated to `password_verify`,
 * which is constant-time. A 6-digit code is low-entropy, but it is defended by three layers the
 * hash alone is not: a short TTL, a hard attempt cap, and single-use consumption — so even a
 * leaked hash is near-worthless once the code has expired or been consumed.
 */
final class OtpService
{
    public const CODE_LENGTH = 6;

    /** Code validity: 10 minutes. */
    public const TTL_SECONDS = 600;

    /** Wrong guesses allowed before the code is dead and a new one must be requested. */
    public const MAX_ATTEMPTS = 5;

    /**
     * A cryptographically-random, zero-padded numeric code, e.g. "004271".
     */
    public function generateCode(): string
    {
        $max = (10 ** self::CODE_LENGTH) - 1;

        return str_pad((string) random_int(0, $max), self::CODE_LENGTH, '0', STR_PAD_LEFT);
    }

    public function hash(string $code): string
    {
        return password_hash($code, PASSWORD_DEFAULT);
    }

    /**
     * Constant-time comparison via password_verify.
     */
    public function verify(string $code, string $hash): bool
    {
        if ($hash === '') {
            return false;
        }

        return password_verify($code, $hash);
    }

    /**
     * Absolute expiry for a code issued "now".
     */
    public function expiryFrom(\DateTimeImmutable $now): \DateTimeImmutable
    {
        return $now->add(new \DateInterval('PT' . self::TTL_SECONDS . 'S'));
    }
}
