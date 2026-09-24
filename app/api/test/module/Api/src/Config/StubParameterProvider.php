<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Config;

use Dvsa\LaminasConfigCloudParameters\ParameterProvider\ParameterProviderInterface;

/**
 * Stands in for Parameter Store / Secrets Manager: answers every name in $parameters with $value.
 */
final class StubParameterProvider implements ParameterProviderInterface
{
    /** @var list<string> */
    public static array $parameters = [];

    /** Valid for every cast in use, so casts can be asserted on. */
    public static string $value = '1';

    #[\Override]
    public static function create(array $config): self
    {
        return new self();
    }

    #[\Override]
    public function __invoke(string $id): array
    {
        return array_fill_keys(self::$parameters, self::$value);
    }
}
