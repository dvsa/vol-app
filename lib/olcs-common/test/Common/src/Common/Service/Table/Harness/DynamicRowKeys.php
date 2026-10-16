<?php

declare(strict_types=1);

namespace CommonTest\Common\Service\Table\Harness;

/**
 * Row keys that are read through a variable, and so are invisible to a search for $row['literal'].
 *
 * Both harnesses build their probe row by harvesting quoted subscripts out of source. That finds
 * every key spelled directly and misses every key that is not, and the miss is silent in the worst
 * possible way: a key the row does not carry is absent, `isset()` on it is false, the branch that
 * would have interpolated it never runs, and the table renders clean. Green, and nothing tested.
 *
 * Formatter\Address is the worked example. Its field names live in a class property:
 *
 *     protected $formats = ['FULL' => ['addressLine1', 'addressLine2', 'town', ...]];
 *     foreach ($fields as $item) {
 *         if (!isset($data[$item])) { continue; }
 *         $parts[] = $data[$item];
 *     }
 *
 * Nothing in that file reads $data['addressLine1'], so the harvest never added it, so every
 * iteration hit the `continue` and $parts came back empty. The formatter was recorded as exercised
 * while the only line that matters — the Escape::html() in formatAddress() — had never run.
 *
 * So where a file reads the row through a variable, the quoted identifier lists in that same file
 * are taken as candidate keys. That is a heuristic, and it is deliberately a narrow one:
 *
 *   - only files that actually do dynamic row access contribute anything, which is 19 of the 149
 *     formatters, and only a handful of those have a list to find
 *   - only identifier-shaped strings in comma-separated array literals count, so a lone 'FULL' or
 *     a CSS class does not become a row key
 *
 * Extra keys are cheap and missing keys are expensive, which is what justifies guessing at all. A
 * key the code never reads is an unused probe and changes nothing. A key it does read is a branch
 * that starts being tested. The failure direction is loud — a value the row should not have had
 * reaching the output is reported as a leak, and a human then decides — rather than the silent
 * pass this replaces.
 */
final class DynamicRowKeys
{
    /**
     * Whether the source reads the row through anything other than a quoted literal.
     *
     * $column is excluded: `$data[$column['name']]` is resolved properly elsewhere, from the column
     * config the formatter was actually handed, so treating it as dynamic here would pull in every
     * list in two thirds of the formatters to no purpose.
     */
    private static function readsRowDynamically(string $source): bool
    {
        return preg_match('/\$(?:row|data)\s*\[\s*\$(?!column\b)[A-Za-z_][A-Za-z0-9_]*/', $source) === 1;
    }

    /**
     * Candidate row keys from a source file, or none when it does not need them.
     *
     * @return string[]
     */
    public static function forSource(string $source): array
    {
        if (!self::readsRowDynamically($source)) {
            return [];
        }

        // A comma-separated list of quoted identifiers, which is the shape a list of field names
        // takes whether it is a class property, a local, or written inline at the call.
        if (
            !preg_match_all(
                '/\[\s*((?:[\'"][A-Za-z_][A-Za-z0-9_]*[\'"]\s*,\s*)+[\'"][A-Za-z_][A-Za-z0-9_]*[\'"])\s*,?\s*\]/',
                $source,
                $lists
            )
        ) {
            return [];
        }

        $keys = [];

        foreach ($lists[1] as $list) {
            if (preg_match_all('/[\'"]([A-Za-z_][A-Za-z0-9_]*)[\'"]/', $list, $names)) {
                $keys = array_merge($keys, $names[1]);
            }
        }

        return array_values(array_unique($keys));
    }

    /**
     * Candidate row keys from a class and every parent it inherits behaviour from.
     *
     * A subclass commonly reads keys declared in an abstract base, so stopping at the class itself
     * would miss exactly the inherited loops this exists to reach.
     *
     * @param class-string $class
     * @return string[]
     */
    public static function forClass(string $class): array
    {
        $keys = [];

        for ($reflection = new \ReflectionClass($class); $reflection !== false; $reflection = $reflection->getParentClass()) {
            $file = $reflection->getFileName();

            if ($file === false || !is_readable($file)) {
                continue;
            }

            $keys = array_merge($keys, self::forSource((string)file_get_contents($file)));
        }

        return array_values(array_unique($keys));
    }
}
