<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\Letter;

/**
 * Records what happened to each [[GRAB]] token during a render.
 *
 * A grab that resolves to nothing is stripped, so the preview looks clean while the real letter
 * carries a hole. Collecting the outcomes lets the builder's diagnostics say so. Deliberately a
 * mutable bag passed down through the render context: the replacement service stays stateless
 * and the ordinary generation paths, which pass no collector, pay nothing.
 */
class GrabOutcomeCollector
{
    public const RESOLVED = 'resolved';
    public const EMPTY = 'empty';
    public const UNKNOWN = 'unknown';

    /** The key the collector travels under inside the vol-grab context array. */
    public const CONTEXT_KEY = '_grabOutcomes';

    /** Worse outcomes must not be papered over by later successes elsewhere in the letter. */
    private const PRECEDENCE = [self::RESOLVED => 0, self::EMPTY => 1, self::UNKNOWN => 2];

    /** @var array<string, string> token => outcome */
    private array $outcomes = [];

    public function record(string $token, string $outcome): void
    {
        $current = $this->outcomes[$token] ?? null;

        if ($current === null || self::PRECEDENCE[$outcome] > self::PRECEDENCE[$current]) {
            $this->outcomes[$token] = $outcome;
        }
    }

    /**
     * @return string[] tokens with the given outcome, in first-seen order
     */
    public function tokensWith(string $outcome): array
    {
        return array_keys(array_filter($this->outcomes, static fn(string $o) => $o === $outcome));
    }
}
