<?php

declare(strict_types=1);

namespace CommonTest\Common\Service\Table\Harness;

use PHPUnit\Framework\TestCase;

/**
 * Asserts that no formatter starts emitting an unescaped row value.
 *
 * Same contract as TableEscapingInvariantTestCase, one level down: a baseline of what leaks today,
 * which may only shrink, and a new leak fails immediately. Because the formatter directory is
 * scanned rather than listed, a formatter added later is covered without anyone remembering this
 * exists.
 *
 * Skips are compared as well as leaks, and that is the part worth understanding. A formatter that
 * cannot be driven is recorded, not ignored — otherwise a formatter that quietly stops running
 * looks identical to one that passes, and its entry sits in the skip list long after the reason for
 * it disappeared. Comparing the set means a formatter moving into *or out of* skipped fails until
 * someone looks.
 */
abstract class FormatterEscapingInvariantTestCase extends TestCase
{
    abstract protected function baselineFile(): string;

    public function testNoFormatterEmitsAnUnescapedRowValue(): void
    {
        $result = new FormatterEscapingHarness()->inspect();

        $this->assertNotSame([], $result['exercised'], 'No formatters ran — the harness is broken.');

        $baseline = $this->entries('leaking');
        $leaking = array_keys($result['leaking']);

        $newLeaks = array_values(array_diff($leaking, $baseline));
        $this->assertSame([], $newLeaks, sprintf(
            "These formatters emit an unescaped row value and are not in the baseline:\n  %s\n\n"
            . "Escape the interpolated values in the formatter. Adding it to %s is not the fix —\n"
            . "the baseline only shrinks.",
            implode("\n  ", $newLeaks),
            basename($this->baselineFile()),
        ));

        // Only a formatter that actually ran can be judged fixed; one that moved into the skipped
        // list is unknown, not resolved.
        $fixed = array_values(array_intersect(array_diff($baseline, $leaking), $result['exercised']));
        $this->assertSame([], $fixed, sprintf(
            "These formatters no longer leak. Remove them from %s so it stays accurate:\n  %s",
            basename($this->baselineFile()),
            implode("\n  ", $fixed),
        ));
    }

    public function testEveryValueIsProvedSafeOneWayOrTheOther(): void
    {
        $result = new FormatterEscapingHarness()->inspect();

        $unproven = [];

        foreach ($result['unproven'] as $formatter => $keys) {
            foreach ($keys as $key => $reason) {
                $unproven[] = "{$formatter}.{$key} ({$reason})";
            }
        }

        sort($unproven);

        // A value that cannot carry the payload is put back on its own and comes back either
        // escaped or rejected by its own type. Both are proofs, so neither is recorded and there is
        // no third category to explain: a value is safe or it is not.
        //
        // This is the case where neither was established. No baseline to add to on purpose — it is
        // empty today, and an entry means someone has to look.
        $this->assertSame([], $unproven, sprintf(
            "These values could not be shown to be safe either way:\n  %s\n\n"
            . "Each was formatted with the payload restored, and the call failed for a reason that\n"
            . "is not its type rejecting it — so it neither reached the output nor was proven unable\n"
            . "to. That is unknown, not safe, and unknown is what this test exists to prevent.\n\n"
            . "Usually the formatter is broken for an unrelated reason: a missing service, or a\n"
            . "throw before it gets near the value. Fix that and the value goes back to being\n"
            . "proved one way or the other.",
            implode("\n  ", $unproven),
        ));
    }

    public function testTheSetOfUndrivableFormattersHasNotChanged(): void
    {
        $result = new FormatterEscapingHarness()->inspect();

        $expected = $this->entries('skipped');
        $actual = array_keys($result['skipped']);
        sort($expected);
        sort($actual);

        $this->assertSame($expected, $actual, sprintf(
            "The set of formatters the probe cannot drive has changed.\n\n"
            . "Newly undrivable: %s\nNow drivable:     %s\n\n"
            . "A formatter becoming undrivable silently removes it from the leak assertion, so this\n"
            . "fails rather than letting coverage shrink unnoticed. One becoming drivable is good\n"
            . "news — move it out of the skipped section of %s.",
            implode(', ', array_diff($actual, $expected)) ?: '(none)',
            implode(', ', array_diff($expected, $actual)) ?: '(none)',
            basename($this->baselineFile()),
        ));
    }

    /**
     * Baseline sections, so leaking and skipped live in one reviewable file.
     *
     * @return string[]
     */
    private function entries(string $section): array
    {
        $file = $this->baselineFile();

        if (!file_exists($file)) {
            return [];
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        $entries = [];
        $current = null;

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            if (str_starts_with($line, '[')) {
                $current = trim($line, '[]');
                continue;
            }

            if (str_starts_with($line, '#')) {
                continue;
            }

            if ($current === $section) {
                $entries[] = $line;
            }
        }

        return $entries;
    }
}
