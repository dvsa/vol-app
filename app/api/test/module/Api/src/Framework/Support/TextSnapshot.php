<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Framework\Support;

use PHPUnit\Framework\Assert;

/**
 * Committed raw-text snapshots: rendered HTML or plain text, stored byte for byte so a diff reads like the output.
 *
 * Same convention as JsonSnapshot: `UPDATE_SNAPSHOTS=1` rewrites the files instead of failing; review the diff
 * before committing it.
 *
 * Files end in `.txt` on purpose. The pre-commit hook runs `prettier --ignore-unknown --write` on every staged
 * file, and prettier would reformat `.html`; it leaves `.txt` alone, so the stored bytes stay the rendered bytes.
 * Nothing is trimmed or normalised either way: trailing whitespace and a missing final newline are part of
 * the output being pinned.
 */
final class TextSnapshot
{
    public const string EXTENSION = '.txt';

    public static function assertFileMatches(string $file, string $actual, string $subject, string $updateCommand): void
    {
        if (JsonSnapshot::isUpdating()) {
            self::write($file, $actual);
        }

        Assert::assertFileExists(
            $file,
            sprintf('%s has no snapshot. Create it with `%s` and review the new file.', $subject, $updateCommand)
        );

        Assert::assertSame(
            (string) file_get_contents($file),
            $actual,
            sprintf(
                '%s no longer matches its snapshot. If the change is intended, run `%s` and review the diff.',
                $subject,
                $updateCommand
            )
        );
    }

    /**
     * Compares a whole directory of snapshots at once: every file in $actual must be stored with the same
     * bytes, and every stored file must still be produced. Fails naming every file that differs.
     *
     * @param array<string, string> $actual file name relative to $directory => rendered output
     */
    public static function assertDirectoryMatches(
        string $directory,
        array $actual,
        string $subject,
        string $updateCommand
    ): void {
        ksort($actual);

        if (JsonSnapshot::isUpdating()) {
            foreach (array_diff_key(self::read($directory), $actual) as $orphan => $content) {
                unlink($directory . '/' . $orphan);
            }

            foreach ($actual as $name => $content) {
                self::write($directory . '/' . $name, $content);
            }
        }

        $expected = self::read($directory);

        Assert::assertSame(
            [],
            array_keys(array_diff_key($actual, $expected)),
            sprintf(
                '%s: these have no snapshot. Create them with `%s` and review the new files.',
                $subject,
                $updateCommand
            )
        );

        Assert::assertSame(
            [],
            array_keys(array_diff_key($expected, $actual)),
            sprintf('%s: these snapshots are no longer produced. Delete them, or run `%s`.', $subject, $updateCommand)
        );

        $changed = array_keys(array_filter(
            $actual,
            static fn(string $content, string $name): bool => $expected[$name] !== $content,
            ARRAY_FILTER_USE_BOTH
        ));

        if ($changed === []) {
            Assert::assertSame($expected, $actual);

            return;
        }

        // Show the first difference in full, and name every file that differs
        Assert::assertSame(
            $expected[$changed[0]],
            $actual[$changed[0]],
            sprintf(
                "%s no longer matches its snapshots. If the change is intended, run `%s` and review the diff.\n"
                . "Changed: %s\nShowing %s:",
                $subject,
                $updateCommand,
                implode(', ', $changed),
                $changed[0]
            )
        );
    }

    /**
     * @return array<string, string> file name relative to $directory => content, sorted by name
     */
    private static function read(string $directory): array
    {
        $files = [];

        if (!is_dir($directory)) {
            return $files;
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS)
        );

        foreach ($iterator as $file) {
            assert($file instanceof \SplFileInfo);
            $name = substr($file->getPathname(), strlen($directory) + 1);
            $files[$name] = (string) file_get_contents($file->getPathname());
        }

        ksort($files);

        return $files;
    }

    private static function write(string $file, string $content): void
    {
        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0777, true);
        }

        file_put_contents($file, $content);
    }
}
