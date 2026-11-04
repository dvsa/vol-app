<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Framework\Support;

/**
 * Turns values read from the running application into something safe to commit as a snapshot: the same on
 * every machine, in CI, and on every run.
 *
 *  - the app (app/api) and repository roots become {app} and {repo}; libs are symlinked from
 *    vendor/olcs/* into lib/, so their __DIR__ paths resolve under the repository, not the app
 *  - the system temp directory (macOS /var/folders/..., Linux /tmp) becomes {tmp}
 *  - closures and objects, which cannot be encoded, become "(closure)" and "(object Fqcn)"
 */
final class PortableValues
{
    public static function of(mixed $value): mixed
    {
        return match (true) {
            is_array($value) => array_map(self::of(...), $value),
            $value instanceof \Closure => '(closure)',
            // get_debug_type() names anonymous classes without their file path and line
            is_object($value) => '(object ' . get_debug_type($value) . ')',
            is_string($value) => self::string($value),
            default => $value,
        };
    }

    public static function string(string $value): string
    {
        // The app root first: it starts with the repository root
        $value = str_replace([self::appRoot(), self::repoRoot()], ['{app}', '{repo}'], $value);

        // Only as a whole path: on Linux the temp directory is /tmp, which other strings can contain
        $tmp = rtrim(sys_get_temp_dir(), '/');

        if ($value === $tmp || str_starts_with($value, $tmp . '/')) {
            $value = '{tmp}' . substr($value, strlen($tmp));
        }

        return $value;
    }

    /**
     * The first line of an exception or warning message, without the details that change with every edit or
     * run: line numbers ("called in {app}/x.php on line 51") and Mockery's numbered class names.
     */
    public static function message(string $message): string
    {
        $firstLine = strtok($message, "\n");
        $firstLine = self::string($firstLine === false ? '' : $firstLine);
        $firstLine = (string) preg_replace('/ on line \d+/', '', $firstLine);

        return (string) preg_replace('/\bMockery_\d+_/', 'Mockery_', $firstLine);
    }

    public static function appRoot(): string
    {
        return ApiApplication::root();
    }

    public static function repoRoot(): string
    {
        return dirname(self::appRoot(), 2);
    }
}
