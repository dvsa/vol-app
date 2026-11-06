<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Utils\Development;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Names the Laminas configuration cache after the files the configuration is built from, so that
 * local development can cache the merged configuration and still see configuration changes.
 *
 * Every call checks the files found by the last scan: editing or deleting one changes the key
 * straight away. Finding new files means listing directories, which is slow on a bind mount, so the
 * directories are scanned again only when the last scan is more than a couple of seconds old. When
 * the key changes, cache files written under any other key are deleted.
 *
 * Not for production, where configuration doesn't change under a running app.
 */
final class ConfigCacheKey
{
    private const string KEY_PREFIX = 'development-';
    private const string MANIFEST_FILE = 'development-config-sources.json';
    private const int DEFAULT_RESCAN_AFTER_SECONDS = 2;

    /**
     * @param string   $cacheDir           Laminas module listener cache_dir
     * @param string[] $directoryPatterns  glob patterns for directories whose PHP files, at any depth, build the configuration
     * @param string[] $filePatterns       glob patterns for other files that build the configuration
     * @param int      $rescanAfterSeconds how old the last scan can be before the directories are listed again
     */
    public function __construct(
        private readonly string $cacheDir,
        private readonly array $directoryPatterns,
        private readonly array $filePatterns,
        private readonly int $rescanAfterSeconds = self::DEFAULT_RESCAN_AFTER_SECONDS,
    ) {
    }

    /**
     * The configuration sources of a VOL Laminas application
     */
    public static function forApplication(
        string $appRoot,
        int $rescanAfterSeconds = self::DEFAULT_RESCAN_AFTER_SECONDS,
    ): self {
        return new self(
            $appRoot . '/data/cache',
            [
                $appRoot . '/config',
                $appRoot . '/module/*/config',
                $appRoot . '/vendor/olcs/*/config',
                $appRoot . '/vendor/olcs/*/Common/config',
            ],
            [
                $appRoot . '/module/*/Module.php',
                $appRoot . '/module/*/src/Module.php',
                $appRoot . '/vendor/olcs/*/Module.php',
                $appRoot . '/vendor/olcs/*/src/Module.php',
                $appRoot . '/vendor/olcs/*/Common/src/Module.php',
                // Composer rewrites this on every install or update, so it stands in for the configuration of
                // every third-party module
                $appRoot . '/vendor/composer/installed.php',
            ],
            $rescanAfterSeconds,
        );
    }

    public function key(): string
    {
        clearstatcache();

        $manifest = $this->readManifest();

        if ($manifest !== null && !$this->isScanDue($manifest['scannedAt']) && $this->areUnchanged($manifest['files'])) {
            return $manifest['key'];
        }

        $files = $this->scan();
        $key = self::KEY_PREFIX . substr(hash('xxh128', json_encode($files, JSON_THROW_ON_ERROR)), 0, 16);

        $this->writeManifest(['key' => $key, 'scannedAt' => time(), 'files' => $files]);

        if ($manifest === null || $manifest['key'] !== $key) {
            $this->deleteCachesOtherThan($key);
        }

        return $key;
    }

    /**
     * @return array{key: string, scannedAt: int, files: array<string, string>}|null
     */
    private function readManifest(): ?array
    {
        $contents = @file_get_contents($this->manifestPath());

        if ($contents === false) {
            return null;
        }

        $manifest = json_decode($contents, true);

        if (
            !is_array($manifest)
            || !is_string($manifest['key'] ?? null)
            || !is_int($manifest['scannedAt'] ?? null)
            || !is_array($manifest['files'] ?? null)
        ) {
            return null;
        }

        /** @var array{key: string, scannedAt: int, files: array<string, string>} $manifest */
        return $manifest;
    }

    private function isScanDue(int $scannedAt): bool
    {
        return time() - $scannedAt >= $this->rescanAfterSeconds;
    }

    /**
     * @param array<string, string> $files path => signature, as recorded by the last scan
     */
    private function areUnchanged(array $files): bool
    {
        foreach ($files as $path => $signature) {
            if ($this->signature($path) !== $signature) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<string, string> path => signature of every file that builds the configuration
     */
    private function scan(): array
    {
        $files = [];

        foreach ($this->directoryPatterns as $pattern) {
            foreach (glob($pattern, GLOB_ONLYDIR) ?: [] as $directory) {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
                );

                /** @var SplFileInfo $file */
                foreach ($iterator as $file) {
                    if ($file->isFile() && $file->getExtension() === 'php') {
                        $files[$file->getPathname()] = $file->getMTime() . ':' . $file->getSize();
                    }
                }
            }
        }

        foreach ($this->filePatterns as $pattern) {
            foreach (glob($pattern) ?: [] as $path) {
                $signature = $this->signature($path);

                if ($signature !== null) {
                    $files[$path] = $signature;
                }
            }
        }

        ksort($files);

        return $files;
    }

    private function signature(string $path): ?string
    {
        $stat = @stat($path);

        return $stat === false ? null : $stat['mtime'] . ':' . $stat['size'];
    }

    /**
     * @param array{key: string, scannedAt: int, files: array<string, string>} $manifest
     */
    private function writeManifest(array $manifest): void
    {
        // Write then rename, so a concurrent request never reads a half-written manifest. A cache
        // directory that can't be written to only costs a scan on every request.
        $temporaryPath = $this->manifestPath() . '.' . getmypid() . '.tmp';

        if (@file_put_contents($temporaryPath, json_encode($manifest, JSON_THROW_ON_ERROR)) !== false) {
            @rename($temporaryPath, $this->manifestPath());
        }
    }

    private function deleteCachesOtherThan(string $key): void
    {
        foreach (glob($this->cacheDir . '/module-*.' . self::KEY_PREFIX . '*.php') ?: [] as $cacheFile) {
            if (!str_ends_with($cacheFile, '.' . $key . '.php')) {
                @unlink($cacheFile);
            }
        }
    }

    private function manifestPath(): string
    {
        return $this->cacheDir . '/' . self::MANIFEST_FILE;
    }
}
