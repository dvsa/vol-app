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
 * it would defeat the exercise.
 *
 * The outcome is binary: the value is safe, or it is not. Safe arrives two ways, and neither is
 * weaker than the other, so neither is recorded anywhere:
 *
 *   escaped      the render survived and the payload came out escaped.
 *   constrained  the type rejected the payload before anything was written, so it cannot reach the
 *                page at all. In production the same input is a 500, not a payload on a page.
 *
 * Calling the second one safe is a complete argument rather than a lenient one, and it is worth
 * spelling out because it looks like a let-off. The constraint partitions every possible value:
 * one that satisfies it is a number or a strict ATOM date, and neither can contain "<", ">", "&"
 * or a quote — the rendered output is derived from the parsed value, not copied from the input.
 * One that does not satisfy it is rejected before a byte is written. There is no third case, so
 * there is nothing left to test and nothing to keep a list of.
 *
 * Not safe also arrives two ways, and both fail:
 *
 *   leaking      the payload reached the output raw. A real leak, and one nothing else could see:
 *                the ordinary run substitutes this value away before it gets there.
 *   unproven     the render failed for a reason that is not the type rejecting the value, so
 *                nothing was established either way. Reported rather than assumed benign.
 *
 * Two things stop "it threw" from being read as "it is safe" when it is not. The first is *why* it
 * threw — see verdict(). The second is *when*: the output buffer is read even on a throw, because a
 * value echoed by one column and only later rejected by another is already on the page, and the
 * exception does not retract it.
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

    /** The type rejected the payload before anything was written: it cannot reach the page. */
    public const CONSTRAINED = 'constrained';

    /** The render survived and the value came out escaped. */
    public const ESCAPED = 'escaped';

    /** The render failed for a reason that says nothing about the value. Not a result. */
    public const UNPROVEN = 'unproven';

    /**
     * What one isolated render says about the value.
     *
     * The leak test comes first and applies whether or not the render finished, because output
     * written before an exception has still been written.
     *
     * The rest turns on *why* it threw, and that distinction is the difference between a proof and
     * a coincidence. "It threw, so the value cannot get out" only holds when the throw is the type
     * rejecting the payload. An exception from somewhere else — a missing service, a formatter
     * broken by an unrelated change — would otherwise be read as proof of safety, and would go on
     * reading that way for as long as the unrelated breakage lasted. That is a false negative in
     * the one place that must not have them, so an unrecognised failure is UNPROVEN and says so.
     *
     * $rejectedTheType is the same question the adaptation loop asks to learn a constraint from a
     * failure, which is what makes this exact rather than a guess about exception classes: the
     * message either names a type the value has to satisfy, or it does not.
     */
    public static function verdict(
        string $output,
        ?\Throwable $thrown,
        string $marker,
        bool $rejectedTheType = false
    ): string {
        if (self::leaks($output, $marker)) {
            return self::LEAKING;
        }

        if ($thrown === null) {
            return self::ESCAPED;
        }

        return $rejectedTheType ? self::CONSTRAINED : self::UNPROVEN;
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
