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

    public function testTheSetOfUnprobedValuesHasNotChanged(): void
    {
        $result = new FormatterEscapingHarness()->inspect();

        $expected = $this->entries('unprobed');
        $actual = [];

        foreach ($result['unprobed'] as $formatter => $keys) {
            foreach ($keys as $key => $type) {
                $actual[] = "{$formatter}.{$key}={$type}";
            }
        }

        sort($expected);
        sort($actual);

        $this->assertSame($expected, $actual, sprintf(
            "The set of values the probe could not carry a payload into has changed.\n\n"
            . "Newly unprobed: %s\nNow probed:     %s\n\n"
            . "A value that has to be a number or a date cannot also contain a payload, so these\n"
            . "columns render but are not asserted. That is a coverage gap, and it is listed rather\n"
            . "than absorbed so it cannot grow quietly. One disappearing is good news — the\n"
            . "constraint went away and the value is fully probed again; remove it from %s.",
            implode(', ', array_diff($actual, $expected)) ?: '(none)',
            implode(', ', array_diff($expected, $actual)) ?: '(none)',
            basename($this->baselineFile()),
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
