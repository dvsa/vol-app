<?php

declare(strict_types=1);

namespace CommonTest\Common\Service\Table\Harness;

use PHPUnit\Framework\TestCase;

/**
 * Fails when a table's rendered output changes.
 *
 * This exists because the escaping work has two failure modes and only one of them was tested.
 *
 *   still leaking   -> TableEscapingInvariantTest catches it. Invisible to users.
 *   double-escaped  -> nothing caught it. Visible to users as literal &lt;b&gt; on the page.
 *
 * During the table escaping migration the second direction was checked by rendering everything
 * against benign data before and after each slice and diffing. It found two real mistakes that
 * review had passed: a bulk transform that wrapped another formatter's rendered output, and a
 * variable that had already been escaped at its assignment. Those checks were throwaway scripts.
 * This is the same check, committed.
 *
 * It is a golden master, so unlike the invariant test it *will* need updating when the UI
 * legitimately changes — that is the point. A failure means "output moved, confirm you meant it".
 * Update by re-running with the environment variable below and committing the result.
 *
 * A table that cannot be rendered is recorded as skipped and carries no digest. Skips are compared
 * too: a table that silently stops rendering is a regression in coverage, and without this it would
 * look like a pass.
 */
abstract class TableRenderSnapshotTestCase extends TestCase
{
    private const UPDATE_ENV = 'UPDATE_TABLE_SNAPSHOTS';

    /**
     * @return string[]
     */
    abstract protected function tableDirectories(): array;

    abstract protected function snapshotFile(): string;

    public function testRenderedOutputHasNotChanged(): void
    {
        $result = new TableEscapingHarness()->snapshot($this->tableDirectories());

        $this->assertNotSame([], $result['digests'], 'No tables rendered — the harness is broken.');

        $actual = $this->format($result);

        if (getenv(self::UPDATE_ENV) !== false) {
            file_put_contents($this->snapshotFile(), $actual);
            $this->markTestSkipped('Snapshot rewritten. Review the diff before committing it.');
        }

        $file = $this->snapshotFile();
        $this->assertFileExists($file, sprintf(
            "No snapshot at %s. Create it with:\n  %s=1 vendor/bin/phpunit %s",
            $file,
            self::UPDATE_ENV,
            static::class
        ));

        $this->assertSame(
            $this->stripComments((string)file_get_contents($file)),
            $this->stripComments($actual),
            sprintf(
                "Table output changed.\n\n"
                . "If the change is intended — a column added, a label reworded — regenerate with:\n"
                . "  %s=1 vendor/bin/phpunit %s\n\n"
                . "If it is not, the likely cause is escaping something that was not a row value: "
                . "developer-authored markup, or another formatter's return value, or a value that "
                . "was already escaped where it was assigned. All three show up here as drift and "
                . "are invisible to the invariant test.",
                self::UPDATE_ENV,
                static::class
            )
        );
    }

    /**
     * @param array{digests: array<string, string>, skipped: array<string, string>} $result
     */
    private function format(array $result): string
    {
        $lines = [
            '# Digest of every table definition rendered against benign data.',
            '#',
            '# Guards the direction the invariant test cannot see: output that changed because',
            '# something which was not a row value got escaped. Regenerate deliberately, never to',
            '# make a red build green.',
            '#',
            sprintf('# %d rendered, %d could not be rendered by the probe.', count($result['digests']), count($result['skipped'])),
            '#',
        ];

        foreach ($result['digests'] as $name => $digest) {
            $lines[] = $digest . '  ' . $name;
        }

        foreach (array_keys($result['skipped']) as $name) {
            $lines[] = 'skipped' . str_repeat(' ', 57) . '  ' . $name;
        }

        return implode("\n", $lines) . "\n";
    }

    /**
     * The counts line moves whenever a table is added, which would otherwise bury the digest change
     * that matters in a diff about arithmetic.
     */
    private function stripComments(string $contents): string
    {
        $lines = array_filter(
            explode("\n", $contents),
            static fn(string $line): bool => $line !== '' && !str_starts_with($line, '#')
        );

        return implode("\n", $lines) . "\n";
    }
}
