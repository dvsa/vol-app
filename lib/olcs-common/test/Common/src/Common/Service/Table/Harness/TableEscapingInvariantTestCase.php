<?php

declare(strict_types=1);

namespace CommonTest\Common\Service\Table\Harness;

use PHPUnit\Framework\TestCase;

/**
 * Asserts that no table starts emitting an unescaped payload.
 *
 * The table render pipeline does not escape — ContentHelper::replaceContent() is a raw str_replace
 * into <td>{{content}}</td> — so escaping currently happens only where an individual formatter
 * chose to do it. Fixing that across ~1050 columns is staged work, so this runs against a baseline
 * of the tables known to leak today rather than failing the build outright.
 *
 * What it is for: a *new* leaking table fails immediately, and because the harness globs the table
 * directory, a table added in eight months is covered without anyone remembering this existed.
 *
 * The baseline is a to-do list, not a permission slip. It should only ever shrink.
 */
abstract class TableEscapingInvariantTestCase extends TestCase
{
    /**
     * Directories to scan for *.table.php definitions.
     *
     * @return string[]
     */
    abstract protected function tableDirectories(): array;

    abstract protected function baselineFile(): string;

    public function testNoTableEmitsAnUnescapedPayload(): void
    {
        $result = new TableEscapingHarness()->inspect($this->tableDirectories());

        $this->assertNotSame([], $result['rendered'], 'No tables rendered — the harness is broken.');

        $baseline = $this->baseline();
        $leaking = array_keys($result['leaking']);

        $newLeaks = array_values(array_diff($leaking, $baseline['leaking']));
        $this->assertSame([], $newLeaks, $this->newLeakMessage($newLeaks));

        // Only tables that actually rendered can be judged fixed; a table that moved into the
        // skipped list is unknown, not resolved.
        $fixed = array_values(array_intersect(array_diff($baseline['leaking'], $leaking), $result['rendered']));
        $this->assertSame([], $fixed, $this->fixedMessage($fixed));

        // A value that cannot carry the payload is put back on its own and comes back either
        // escaped or rejected by its own type. Both are proofs, so neither is recorded and there
        // is no third category to explain: a value is safe or it is not.
        //
        // This is the case where neither was established — the isolated render failed for a reason
        // that says nothing about the value, so "it threw" is not evidence of anything. No baseline
        // to add to on purpose: it is empty today and an entry means someone has to look.
        $this->assertSame(
            [],
            $this->unprovenLines($result['unproven']),
            "These values could not be shown to be safe either way.\n\n"
            . "Each was rendered with the payload restored, and the render failed for a reason that\n"
            . "is not its type rejecting it — so it neither reached the output nor was proven unable\n"
            . "to. That is unknown, not safe, and unknown is what this test exists to prevent.\n\n"
            . "Usually the render is broken for an unrelated reason: a missing service, or a\n"
            . "formatter that throws before it gets near the value. Fix that and the value goes\n"
            . "back to being proved one way or the other."
        );
    }

    /**
     * The unproven map as one sorted "table key: reason" line per value.
     *
     * @param array<string, array<string, string>> $unproven
     * @return string[]
     */
    private function unprovenLines(array $unproven): array
    {
        $lines = [];

        foreach ($unproven as $table => $values) {
            foreach ($values as $key => $reason) {
                $lines[] = sprintf('%s %s (%s)', $table, $key, $reason);
            }
        }

        sort($lines);

        return $lines;
    }

    /**
     * @return array{leaking: string[]}
     */
    private function baseline(): array
    {
        $file = $this->baselineFile();
        $sections = ['leaking' => []];

        if (!file_exists($file)) {
            return $sections;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        // Every bracketed line is a section header, not just the one recognised below. A header
        // this does not know about therefore *stops* collection rather than falling through into
        // the current section, so a stale file left over from an older format reads as an empty
        // section instead of silently turning its contents into leak entries.
        $current = 'leaking';

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (str_starts_with($line, '[')) {
                $current = trim($line, '[]');
                continue;
            }

            if (isset($sections[$current])) {
                $sections[$current][] = $line;
            }
        }

        return $sections;
    }

    /**
     * @param string[] $newLeaks
     */
    private function newLeakMessage(array $newLeaks): string
    {
        return sprintf(
            "These tables emit an unescaped value and are not in the baseline:\n  %s\n\n"
                . "Escape the interpolated values in the formatter or closure concerned. Adding them\n"
                . "to %s is not the fix — the baseline only shrinks.",
            implode("\n  ", $newLeaks),
            basename($this->baselineFile()),
        );
    }

    /**
     * @param string[] $fixed
     */
    private function fixedMessage(array $fixed): string
    {
        return sprintf(
            "These tables no longer leak. Remove them from %s so it stays accurate:\n  %s",
            basename($this->baselineFile()),
            implode("\n  ", $fixed),
        );
    }
}
