<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Utils\Translation;

/**
 * Token replacements applied to translated messages.
 *
 * Replaces literal substrings (typically `{{token}}`) in a translated string.
 * Held as an injected dependency of the translator so the substitution rules
 * are explicit rather than baked into the translator class.
 */
final readonly class Replacements
{
    /**
     * @param array<string, string> $map
     */
    public function __construct(private array $map)
    {
    }

    public function apply(string $message): string
    {
        if ($this->map === []) {
            return $message;
        }

        return str_replace(array_keys($this->map), array_values($this->map), $message);
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return $this->map;
    }
}
