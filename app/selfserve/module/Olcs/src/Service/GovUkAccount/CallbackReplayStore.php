<?php

declare(strict_types=1);

namespace Olcs\Service\GovUkAccount;

/**
 * Tracks which GOV.UK One Login authorisation codes have already been handled.
 * Codes are single use, so a repeated callback must replay the first outcome
 * rather than attempt a second token exchange.
 */
class CallbackReplayStore
{
    public const TTL_SECONDS = 60;

    private const KEY_PREFIX = 'govuk-account-callback:';
    private const IN_PROGRESS = 'in-progress';

    public function __construct(private readonly \Redis $redis)
    {
    }

    public function claim(string $code): CallbackClaim
    {
        if ($code === '') {
            return CallbackClaim::claimed();
        }

        try {
            $won = $this->redis->set(
                $this->key($code),
                self::IN_PROGRESS,
                ['nx', 'ex' => self::TTL_SECONDS]
            );

            if ($won) {
                return CallbackClaim::claimed();
            }

            $stored = $this->redis->get($this->key($code));
        } catch (\RedisException) {
            // Never block signing on the cache.
            return CallbackClaim::claimed();
        }

        if (is_string($stored) && $stored !== '' && $stored !== self::IN_PROGRESS) {
            return CallbackClaim::replayOf($stored);
        }

        return CallbackClaim::replayInFlight();
    }

    public function recordOutcome(string $code, string $redirectUrl): void
    {
        if ($code === '') {
            return;
        }

        try {
            $this->redis->set($this->key($code), $redirectUrl, ['ex' => self::TTL_SECONDS]);
        } catch (\RedisException) {
            // A lost write only means a later replay behaves as it does today.
        }
    }

    private function key(string $code): string
    {
        return self::KEY_PREFIX . hash('sha256', $code);
    }
}
