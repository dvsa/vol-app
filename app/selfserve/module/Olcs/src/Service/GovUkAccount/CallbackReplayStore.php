<?php

declare(strict_types=1);

namespace Olcs\Service\GovUkAccount;

/**
 * Tracks which GOV.UK One Login authorisation codes have already been handled,
 * and by whom. Codes are single use, so a repeated callback must replay the
 * first outcome rather than attempt a second token exchange. The owning user is
 * stored alongside so one user's destination is never served to another.
 */
class CallbackReplayStore
{
    public const int TTL_SECONDS = 60;

    private const string KEY_PREFIX = 'govuk-account-callback:';

    public function __construct(private readonly \Redis $redis)
    {
    }

    public function claim(string $code, string $userId): CallbackClaim
    {
        if ($code === '' || $userId === '') {
            return CallbackClaim::claimed();
        }

        try {
            $won = $this->redis->set(
                $this->key($code),
                $this->payload($userId, null),
                ['nx', 'ex' => self::TTL_SECONDS]
            );

            if ($won) {
                return CallbackClaim::claimed();
            }

            $stored = $this->redis->get($this->key($code));
        } catch (\RedisException | \JsonException) {
            // Never block signing on the cache.
            return CallbackClaim::claimed();
        }

        // The key expired between the claim attempt and this read.
        if ($stored === false || $stored === null) {
            return CallbackClaim::claimed();
        }

        $decoded = is_string($stored) ? json_decode($stored, true) : null;

        // Unreadable entry: cannot establish ownership, so treat it as foreign.
        if (!is_array($decoded) || !isset($decoded['u'])) {
            return CallbackClaim::foreignReplay();
        }

        if ((string) $decoded['u'] !== $userId) {
            return CallbackClaim::foreignReplay();
        }

        $redirectUrl = $decoded['r'] ?? null;

        return is_string($redirectUrl) && $redirectUrl !== ''
            ? CallbackClaim::replayComplete($redirectUrl)
            : CallbackClaim::replayInFlight();
    }

    public function recordOutcome(string $code, string $userId, string $redirectUrl): void
    {
        if ($code === '' || $userId === '') {
            return;
        }

        try {
            $this->redis->set(
                $this->key($code),
                $this->payload($userId, $redirectUrl),
                ['ex' => self::TTL_SECONDS]
            );
        } catch (\RedisException | \JsonException) {
            // A lost write only means a later replay behaves as it does today.
        }
    }

    private function payload(string $userId, ?string $redirectUrl): string
    {
        return json_encode(['u' => $userId, 'r' => $redirectUrl], JSON_THROW_ON_ERROR);
    }

    private function key(string $code): string
    {
        return self::KEY_PREFIX . hash('sha256', $code);
    }
}
