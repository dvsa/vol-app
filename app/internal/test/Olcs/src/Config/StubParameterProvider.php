<?php

declare(strict_types=1);

namespace OlcsTest\Config;

use Dvsa\LaminasConfigCloudParameters\ParameterProvider\ParameterProviderInterface;

/**
 * Stands in for Parameter Store / Secrets Manager: finds every placeholder in the merged config it is given and
 * answers each one, so there is no list of parameter names to keep in step with the config.
 */
final class StubParameterProvider implements ParameterProviderInterface
{
    /** The syntax Symfony resolves (and the library reports on): %name%, with %% an escaped percent sign. */
    private const string PARAMETER_PATTERN = '/%%|%([^%\s]++)%/';

    /** @var list<string> */
    public static array $supplied = [];

    #[\Override]
    public static function create(array $config): self
    {
        self::$supplied = self::placeholdersIn($config);

        return new self();
    }

    #[\Override]
    public function __invoke(string $id): array
    {
        // '1' is valid for every cast in use, so the casts can be asserted on
        return array_fill_keys(self::$supplied, '1');
    }

    /**
     * Placeholders in keys as well as values, as Symfony resolves both.
     *
     * @param array<array-key, mixed> $config
     *
     * @return list<string>
     */
    public static function placeholdersIn(array $config): array
    {
        $names = [];

        $walk = function (array $node) use (&$walk, &$names): void {
            foreach ($node as $key => $value) {
                foreach ([$key, $value] as $candidate) {
                    if (is_string($candidate) && preg_match_all(self::PARAMETER_PATTERN, $candidate, $matches)) {
                        // An escaped %% produces an empty capture and references nothing
                        array_push($names, ...array_filter($matches[1], static fn(string $name): bool => $name !== ''));
                    }
                }

                if (is_array($value)) {
                    $walk($value);
                }
            }
        };

        $walk($config);

        $names = array_values(array_unique($names));
        sort($names);

        return $names;
    }
}
