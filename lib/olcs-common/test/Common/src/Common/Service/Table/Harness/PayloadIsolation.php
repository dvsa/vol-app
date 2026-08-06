<?php

declare(strict_types=1);

namespace CommonTest\Common\Service\Table\Harness;

/**
 * Turns "this value could not carry the payload" into a verdict about whether it could ever leak.
 *
 * A number or a date cannot also be "<script>…", so any value a type constraint pins down renders
 * without the payload and is not tested for escaping. Recording that as unprobed was honest but it
 * left two things unsaid, and the second is a real hole:
 *
 *   1. "Not tested" is not "safe". It is unknown, and unknown is where a leak hides.
 *   2. The substitution lands in the row every column shares. If one column parses createdOn as a
 *      date and another interpolates it into markup, the date that satisfies the first is what the
 *      second emits — so the substitution *masks* the leak it should have found. The more
 *      constrained a value is, the better it is hidden.
 *
 * So each such value is put back. One at a time, the payload is restored at exactly that path while
 * every other value stays as the settled render left it, and the render is run once more with no
 * adaptation — the point is to see what happens when the constraint is violated, so recovering from
 * it would defeat the exercise. Three things can happen, and all three are answers:
 *
 *   leaking      the payload reached the output raw. A real leak, and one nothing else could see:
 *                the ordinary run substitutes this value away before it gets there.
 *   constrained  the render threw, and had emitted nothing by then. The value cannot reach the
 *                output at all, because the type constraint rejects it first — in production the
 *                same input is a 500, not a payload on a page. That is a proof, not an exemption.
 *   escaped      the render survived and the payload came out escaped. The constraint applied
 *                somewhere else, or on a branch this row does not take, and the value is covered
 *                exactly like any other.
 *
 * "Emitted nothing by then" is the part worth being careful about, and why the output buffer is
 * read even when the render throws. A value that is echoed by one column and only later rejected by
 * another is already on the page; the throw does not retract it. Checking what was written before
 * the exception is what separates that from a genuine constraint.
 *
 * One path at a time, never in a batch, and the conversation formatters show why. getFirstReadBy()
 * compares $row['createdBy']['id'] against $firstRead['user']['id'] and returns early when they
 * match, so restoring the payload to both at once makes them equal, skips the branch, and reports
 * "no leak" about code that never ran.
 */
final class PayloadIsolation
{
    /** The payload reached the output raw. */
    public const LEAKING = 'leaking';

    /** The render threw before emitting anything: the constraint keeps the value off the page. */
    public const CONSTRAINED = 'constrained';

    /** The render survived and the value came out escaped. */
    public const ESCAPED = 'escaped';

    /**
     * What one isolated render says about the value.
     *
     * The leak test comes first and applies whether or not the render finished, because output
     * written before an exception has still been written.
     */
    public static function verdict(string $output, ?\Throwable $thrown, string $marker): string
    {
        if (self::leaks($output, $marker)) {
            return self::LEAKING;
        }

        return $thrown === null ? self::ESCAPED : self::CONSTRAINED;
    }

    /**
     * Whether the marker reached the output unescaped.
     *
     * The same two checks both harnesses apply to an ordinary render: the payload appearing whole,
     * and the payload appearing inside a still-quoted attribute, where it could have closed it.
     */
    public static function leaks(string $output, string $marker): bool
    {
        return str_contains($output, $marker)
            || preg_match('/="[^"]*' . preg_quote($marker, '/') . '/', $output) === 1;
    }

    /**
     * The row with the payload forced back in at one dotted path.
     *
     * Only descends through real arrays, which is exactly the hand-written fixture case — the leaf
     * is a literal there, so nothing else can put the marker back. A path served by a RecursiveProbe
     * is restored by dropping its override instead, before the row is rebuilt, and this returns the
     * row untouched rather than guessing: a probe answers any key with itself, so there is no array
     * here to descend into and writing one would replace the probe with a shape it never had.
     *
     * @param array<array-key, mixed> $row
     * @return array<array-key, mixed>
     */
    public static function withMarkerAt(array $row, string $path, string $marker): array
    {
        $segments = explode('.', $path);
        $last = array_key_last($segments);
        $cursor = &$row;

        foreach ($segments as $i => $segment) {
            if ($i === $last) {
                // Only where something is already there. A key the row does not hold is not this
                // fixture's leaf, and inventing it would probe a value the code never reads.
                if (array_key_exists($segment, $cursor)) {
                    $cursor[$segment] = $marker;
                }

                break;
            }

            if (!isset($cursor[$segment]) || !is_array($cursor[$segment])) {
                return $row;
            }

            $cursor = &$cursor[$segment];
        }

        return $row;
    }
}
