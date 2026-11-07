<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Utils\Development;

use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * Names the Laminas configuration cache after everything the configuration is built from, so that
 * local development can cache the merged configuration and still see configuration changes.
 *
 * The configuration is built from three kinds of source:
 *
 * - Files in configuration directories. These are found by scanning the directories, which also
 *   finds new files that a configuration file loads with glob().
 * - PHP files loaded while the configuration is merged, such as classes whose constants or methods
 *   the configuration uses. ConfigCacheModule records these each time the configuration is merged.
 * - Environment variables, such as the one that loads the developer toolbar. A container's
 *   environment can only change by replacing the container, so the key includes the container's
 *   hostname, which changes when it's replaced. (getenv() can't be used: under PHP-FPM it also
 *   returns the request's FastCGI parameters.)
 *
 * Every call checks the files from the last scan and the last merge, so editing or deleting one
 * changes the key straight away. Listing directories is slow on a bind mount, so they're scanned
 * again only when the last scan is more than a couple of seconds old. The first time a key is used,
 * cache files written under any other key are deleted.
 *
 * Not for production, where configuration doesn't change under a running app.
 */
final class ConfigCacheKey
{
    public const string MODULE_NAME = 'Dvsa\Olcs\Utils\Development\ConfigCache';

    private const string KEY_PREFIX = 'development-';
    private const string MANIFEST_FILE = 'development-config-sources.json';
    private const int DEFAULT_RESCAN_AFTER_SECONDS = 2;

    // Files kept in configuration directories that aren't configuration: view templates, the dist
    // copies of local configuration, and documentation
    private const array IGNORED_EXTENSIONS = ['phtml', 'dist', 'md'];

    private readonly string $appRoot;
    private readonly string $cacheDir;

    /** @var string[]|null */
    private ?array $libraryRoots = null;

    /**
     * @param string $appRoot            the application's root directory
     * @param string $container          identifies the container, whose environment can't change while it runs
     * @param int    $rescanAfterSeconds how old the last scan can be before the directories are listed again
     */
    private function __construct(
        string $appRoot,
        private readonly string $container,
        private readonly int $rescanAfterSeconds,
    ) {
        // get_included_files() reports real paths
        $this->appRoot = realpath($appRoot) ?: $appRoot;
        $this->cacheDir = $this->appRoot . '/data/cache';
    }

    /**
     * @param string|null $container defaults to this host's name
     */
    public static function forApplication(
        string $appRoot,
        int $rescanAfterSeconds = self::DEFAULT_RESCAN_AFTER_SECONDS,
        ?string $container = null,
    ): self {
        return new self($appRoot, $container ?? (gethostname() ?: ''), $rescanAfterSeconds);
    }

    public function key(): string
    {
        $key = $this->keyFor($this->sourcesHash());

        if (!is_file($this->cacheDir . '/module-config-cache.' . $key . '.php')) {
            $this->deleteCachesOtherThan($key);
        }

        return $key;
    }

    /**
     * The module to load alongside the application's modules, so the files loaded while the
     * configuration is merged are recorded
     */
    public function module(): ConfigCacheModule
    {
        return new ConfigCacheModule($this);
    }

    /**
     * Records the application's files that were loaded while the configuration was merged, so that
     * changing any of them changes the key, and returns the key that now applies
     *
     * @param string[] $includedFiles as returned by get_included_files()
     */
    public function recordLoadedFiles(array $includedFiles): string
    {
        clearstatcache();

        $manifest = $this->readManifest();
        $loaded = $this->signatures(array_filter($includedFiles, $this->isApplicationFile(...)));

        if ($manifest === null) {
            return $this->keyFor($this->saveManifest($this->scan(), $loaded, time()));
        }

        return $this->keyFor($this->saveManifest($manifest['files'], $loaded, $manifest['scannedAt']));
    }

    private function keyFor(string $sourcesHash): string
    {
        return self::KEY_PREFIX . substr(hash('xxh128', $sourcesHash . "\0" . $this->container), 0, 16);
    }

    /**
     * A hash of the files that build the configuration, from the last scan if it's still current
     */
    private function sourcesHash(): string
    {
        clearstatcache();

        $manifest = $this->readManifest();

        if (
            $manifest !== null
            && !$this->isScanDue($manifest['scannedAt'])
            && $this->areUnchanged($manifest['files'] + $manifest['loaded'])
        ) {
            return $manifest['sources'];
        }

        $loaded = $this->signatures(array_keys($manifest['loaded'] ?? []));

        return $this->saveManifest($this->scan(), $loaded, time());
    }

    /**
     * @param array<string, string> $files  path => signature of the files found by scanning
     * @param array<string, string> $loaded path => signature of the files loaded by the last merge
     *
     * @return string a hash of every file's signature
     */
    private function saveManifest(array $files, array $loaded, int $scannedAt): string
    {
        $sources = array_merge($files, $loaded);
        ksort($sources);
        $sourcesHash = hash('xxh128', json_encode($sources, JSON_THROW_ON_ERROR));

        $this->writeManifest([
            'sources' => $sourcesHash,
            'scannedAt' => $scannedAt,
            'files' => $files,
            'loaded' => $loaded,
        ]);

        return $sourcesHash;
    }

    /**
     * @return array{sources: string, scannedAt: int, files: array<string, string>, loaded: array<string, string>}|null
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
            || !is_string($manifest['sources'] ?? null)
            || !is_int($manifest['scannedAt'] ?? null)
            || !is_array($manifest['files'] ?? null)
            || !is_array($manifest['loaded'] ?? null)
        ) {
            return null;
        }

        /** @var array{sources: string, scannedAt: int, files: array<string, string>, loaded: array<string, string>} $manifest */
        return $manifest;
    }

    private function isScanDue(int $scannedAt): bool
    {
        return time() - $scannedAt >= $this->rescanAfterSeconds;
    }

    /**
     * @param array<string, string> $files path => signature, as recorded
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
     * @return array<string, string> path => signature of every file found in the configuration's sources
     */
    private function scan(): array
    {
        $paths = [];

        foreach ($this->directoryPatterns() as $pattern) {
            foreach (glob($pattern, GLOB_ONLYDIR) ?: [] as $directory) {
                $iterator = new RecursiveIteratorIterator(
                    new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)
                );

                /** @var SplFileInfo $file */
                foreach ($iterator as $file) {
                    if ($file->isFile() && $this->isConfigurationFile($file)) {
                        $paths[] = $file->getPathname();
                    }
                }
            }
        }

        foreach ($this->filePatterns() as $pattern) {
            $paths = array_merge($paths, glob($pattern) ?: []);
        }

        // The libraries are linked into vendor/, so resolve the links: a file the merge loads is then
        // recorded under the same path as when it's found by scanning
        return $this->signatures(array_unique(array_map(static fn (string $path): string => realpath($path) ?: $path, $paths)));
    }

    /**
     * Directories whose files, at any depth, build the configuration
     *
     * @return string[] glob patterns
     */
    private function directoryPatterns(): array
    {
        return [
            $this->appRoot . '/config',
            $this->appRoot . '/module/*/config',
            $this->appRoot . '/vendor/olcs/*/config',
            $this->appRoot . '/vendor/olcs/*/Common/config',
        ];
    }

    /**
     * Other files that build the configuration, so a module that is added is found by the next scan
     *
     * @return string[] glob patterns
     */
    private function filePatterns(): array
    {
        return [
            $this->appRoot . '/module/*/Module.php',
            $this->appRoot . '/module/*/src/Module.php',
            $this->appRoot . '/vendor/olcs/*/Module.php',
            $this->appRoot . '/vendor/olcs/*/src/Module.php',
            $this->appRoot . '/vendor/olcs/*/Common/src/Module.php',
            // Composer rewrites this on every install or update, so it stands in for the configuration of
            // every third-party module
            $this->appRoot . '/vendor/composer/installed.php',
        ];
    }

    private function isConfigurationFile(SplFileInfo $file): bool
    {
        return !str_starts_with($file->getFilename(), '.')
            && !in_array(strtolower($file->getExtension()), self::IGNORED_EXTENSIONS, true);
    }

    /**
     * Whether a loaded file is part of the application or its in-repository libraries. Third-party
     * packages only change with composer install, which installed.php covers, and data/ holds caches.
     */
    private function isApplicationFile(string $path): bool
    {
        foreach ($this->libraryRoots() as $libraryRoot) {
            if (str_starts_with($path, $libraryRoot . '/')) {
                return !str_starts_with($path, $libraryRoot . '/vendor/');
            }
        }

        $root = $this->appRoot . '/';

        if (!str_starts_with($path, $root)) {
            return false;
        }

        $relativePath = substr($path, strlen($root));

        if (str_starts_with($relativePath, 'data/')) {
            return false;
        }

        return !str_starts_with($relativePath, 'vendor/') || str_starts_with($relativePath, 'vendor/olcs/');
    }

    /**
     * Where the in-repository libraries really are. vendor/olcs/* links to them, and in a container
     * they're mounted wherever those links point.
     *
     * @return string[]
     */
    private function libraryRoots(): array
    {
        return $this->libraryRoots ??= array_values(array_filter(array_map(
            static fn (string $directory): string|false => realpath($directory),
            glob($this->appRoot . '/vendor/olcs/*', GLOB_ONLYDIR) ?: []
        )));
    }

    /**
     * @param string[] $paths
     *
     * @return array<string, string> path => signature, leaving out files that don't exist
     */
    private function signatures(array $paths): array
    {
        $signatures = [];

        foreach ($paths as $path) {
            $signature = $this->signature($path);

            if ($signature !== null) {
                $signatures[$path] = $signature;
            }
        }

        ksort($signatures);

        return $signatures;
    }

    private function signature(string $path): ?string
    {
        $stat = @stat($path);

        return $stat === false ? null : $stat['mtime'] . ':' . $stat['size'];
    }

    /**
     * @param array{sources: string, scannedAt: int, files: array<string, string>, loaded: array<string, string>} $manifest
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
