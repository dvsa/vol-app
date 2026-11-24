<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Framework\Support;

use PHPUnit\Framework\Assert;

/**
 * Committed-JSON snapshots, as DtoSnapshotTest (lib/olcs-transfer) and FormSpecificationSnapshotTest do them.
 *
 * `UPDATE_SNAPSHOTS=1` rewrites the files instead of failing; review the diff before committing it.
 */
final class JsonSnapshot
{
    public static function assertMatches(string $file, mixed $actual, string $subject, string $updateCommand): void
    {
        $encoded = self::encode($actual);

        if (self::isUpdating()) {
            if (!is_dir(dirname($file))) {
                mkdir(dirname($file), 0777, true);
            }

            file_put_contents($file, $encoded);
        }

        Assert::assertFileExists(
            $file,
            sprintf('%s has no snapshot. Create it with `%s` and review the new file.', $subject, $updateCommand)
        );

        // Re-encode the stored file so formatting differences cannot fail the comparison
        $expected = self::encode(json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR));

        Assert::assertSame(
            $expected,
            $encoded,
            sprintf(
                '%s no longer matches its snapshot. If the change is intended, run `%s` and review the diff.',
                $subject,
                $updateCommand
            )
        );
    }

    /**
     * Fails for any snapshot file under $directory that is not in $expectedFiles; deletes them when updating.
     *
     * @param list<string> $expectedFiles
     */
    public static function assertNoOrphans(string $directory, array $expectedFiles, string $updateCommand): void
    {
        $orphans = [];
        $files = is_dir($directory)
            ? new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS))
            : [];

        foreach ($files as $file) {
            if (!in_array($file->getPathname(), $expectedFiles, true)) {
                $orphans[] = $file->getPathname();
            }
        }

        if (self::isUpdating()) {
            array_map(unlink(...), $orphans);
            $orphans = [];
        }

        sort($orphans);

        Assert::assertSame(
            [],
            $orphans,
            sprintf('These snapshots no longer have a test case. Delete them, or run `%s`.', $updateCommand)
        );
    }

    public static function encode(mixed $data): string
    {
        $json = json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION
            | JSON_THROW_ON_ERROR
        );

        // Two-space indentation, per .editorconfig. JSON strings cannot contain raw newlines, so every
        // run of leading spaces is indentation.
        return preg_replace_callback(
            '/^(?: {4})+/m',
            static fn(array $match): string => str_repeat('  ', intdiv(strlen($match[0]), 4)),
            $json
        ) . "\n";
    }

    public static function isUpdating(): bool
    {
        return getenv('UPDATE_SNAPSHOTS') === '1';
    }
}
