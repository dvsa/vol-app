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

        // Values a type constraint stops from carrying the payload. Each one was put back on its
        // own and the render rejected it before writing anything, so the constraint — not an
        // escaping call — is what keeps it off the page. That is a proof rather than an exemption,
        // but it is a narrower one than "this value is escaped", so it stays recorded.
        //
        // Asserted in both directions like the leak list. A new entry means a value that used to be
        // escaped is now merely rejected, which is worth seeing; a stale one means the constraint
        // went away and the value is fully covered again.
        $this->assertSame(
            $baseline['constrained'],
            $this->constrainedLines($result['constrained']),
            "The set of values kept off the page by a type constraint has changed.\n\n"
            . "A new entry means a value stopped being escaped and is now only unreachable —\n"
            . "usually a formatter that started requiring a number or a date. Check by hand that\n"
            . "nothing else emits it before recording it. A disappearing entry means the constraint\n"
            . "went away, so the value is probed like any other and the baseline should shrink."
        );
    }

    /**
     * The constrained map as one sorted "table key=type" line per value.
     *
     * @param array<string, array<string, string>> $constrained
     * @return string[]
     */
    private function constrainedLines(array $constrained): array
    {
        $lines = [];

        foreach ($constrained as $table => $values) {
            foreach ($values as $key => $type) {
                $lines[] = sprintf('%s %s=%s', $table, $key, $type);
            }
        }

        sort($lines);

        return $lines;
    }

    /**
     * @return array{leaking: string[], constrained: string[]}
     */
    private function baseline(): array
    {
        $file = $this->baselineFile();
        $sections = ['leaking' => [], 'constrained' => []];

        if (!file_exists($file)) {
            return $sections;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        $current = 'leaking';

        foreach ($lines as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            if (isset($sections[trim($line, '[]')]) && str_starts_with($line, '[')) {
                $current = trim($line, '[]');
                continue;
            }

            $sections[$current][] = $line;
        }

        sort($sections['constrained']);

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
