<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Retrieval;

/**
 * Issues and validates the short-lived grant a browser holds after passing OTP, so downloads
 * within one session don't re-prompt for a code. This is the stateless, self-expiring role a
 * signed token is actually good at — unlike the link itself, which is a revocable server-side
 * token.
 *
 * Format: `<payload>.<signature>` where payload is base64url(JSON {t, exp}) and signature is a
 * base64url HMAC-SHA256 over the payload. The grant is bound to ONE link token (`t`) so it can
 * never be replayed against a different link, and carries its own expiry (`exp`).
 */
final class SessionGrantService
{
    /** Grant validity: 15 minutes. */
    public const TTL_SECONDS = 900;

    private const MIN_SECRET_LENGTH = 32;

    private string $secret;

    public function __construct(string $secret)
    {
        // No validation here on purpose: gate=none flows construct this service (the Download
        // handler is shared by both gate modes) but never use it, so it MUST be constructable with
        // an empty/unset secret. The secret is validated at use time instead (issue/isValid).
        $this->secret = $secret;
    }

    private function hasUsableSecret(): bool
    {
        return strlen($this->secret) >= self::MIN_SECRET_LENGTH;
    }

    public function issue(string $linkToken, \DateTimeImmutable $now): string
    {
        if (!$this->hasUsableSecret()) {
            throw new \RuntimeException(sprintf(
                'Retrieval session grant secret must be at least %d characters — set retrieval.session_secret',
                self::MIN_SECRET_LENGTH,
            ));
        }

        $payload = self::b64UrlEncode((string) json_encode([
            't' => $linkToken,
            'exp' => $now->getTimestamp() + self::TTL_SECONDS,
        ]));

        return $payload . '.' . self::b64UrlEncode($this->rawSignature($payload));
    }

    /**
     * True only if the grant is well-formed, untampered, unexpired, and bound to $linkToken.
     */
    public function isValid(string $grant, string $linkToken, \DateTimeImmutable $now): bool
    {
        // Without a usable secret no grant can ever be valid (fail closed).
        if (!$this->hasUsableSecret()) {
            return false;
        }

        $parts = explode('.', $grant);
        if (count($parts) !== 2) {
            return false;
        }

        [$payload, $signature] = $parts;

        $expected = self::b64UrlEncode($this->rawSignature($payload));
        if (!hash_equals($expected, $signature)) {
            return false;
        }

        $decoded = json_decode((string) self::b64UrlDecode($payload), true);
        if (!is_array($decoded) || !isset($decoded['t'], $decoded['exp'])) {
            return false;
        }

        if (!hash_equals((string) $decoded['t'], $linkToken)) {
            return false;
        }

        return (int) $decoded['exp'] > $now->getTimestamp();
    }

    private function rawSignature(string $payload): string
    {
        return hash_hmac('sha256', $payload, $this->secret, true);
    }

    private static function b64UrlEncode(string $raw): string
    {
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }

    private static function b64UrlDecode(string $encoded): string
    {
        return (string) base64_decode(strtr($encoded, '-_', '+/'), true);
    }
}
