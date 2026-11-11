<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Utils\Development;

use Closure;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RuntimeException;
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
 * A file is checked by its modification time and size. Modification times are only to the second,
 * so a file changed in the last couple of seconds could change again without either changing: until
 * it's older, a hash of its contents is part of the key as well. Each version of the file then has a
 * cache of its own, which a request still merging an earlier version can't overwrite. Once the file
 * is older, the key changes once more, so the configuration is merged again a few seconds after it
 * last changed.
 *
 * Not for production, where configuration doesn't change under a running app.
 *
 * @phpstan-type Manifest array{
 *     sources: string,
 *     scannedAt: int,
 *     files: array<string, string>,
 *     loaded: array<string, string>,
 * }
 * @psalm-type Manifest = array{
 *     sources: string,
 *     scannedAt: int,
 *     files: array<string, string>,
 *     loaded: array<string, string>,
 * }
 */
final class ConfigCacheKey
{
    public const string MODULE_NAME = 'Dvsa\Olcs\Utils\Development\ConfigCache';

    // Where Laminas writes the configuration cache, within the application
    public const string CACHE_DIRECTORY = 'data/cache';

    // The names Laminas gives its caches of the merged configuration and of where the modules are
    private const string CONFIG_CACHE = 'module-config-cache';
    private const string MODULE_MAP_CACHE = 'module-classmap-cache';

    private const string KEY_PREFIX = 'development-';
    private const string MANIFEST_FILE = 'development-config-sources.json';
    private const int DEFAULT_RESCAN_AFTER_SECONDS = 2;

    // A file modified up to this many seconds ago, by the container's clock, could be modified again
    // within the same second. The margin allows for the host's clock, which sets the modification
    // time, being behind the container's.
    private const int RECENT_CHANGE_SECONDS = 2;

    // Files kept in configuration directories that aren't configuration: view templates, the dist
    // copies of local configuration, and documentation
    private const array IGNORED_EXTENSIONS = ['phtml', 'dist', 'md'];

    private readonly string $appRoot;
    private readonly string $cacheDir;

    /** @var Closure(): int */
    private readonly Closure $clock;

    /** @var string[]|null */
    private ?array $libraryRoots = null;

    /**
     * The manifest the key was last worked out from
     *
     * @var Manifest|null
     */
    private ?array $manifest = null;

    /**
     * @param string                $appRoot            the application's root directory
     * @param string                $container          identifies the container, whose environment can't change
     *                                                  while it runs
     * @param int                   $rescanAfterSeconds how old the last scan can be before the directories are
     *                                                  listed again
     * @param (Closure(): int)|null $clock              the current Unix time, by default time()
     */
    private function __construct(
        string $appRoot,
        private readonly string $container,
        private readonly int $rescanAfterSeconds,
        ?Closure $clock,
    ) {
        // get_included_files() reports real paths
        $this->appRoot = realpath($appRoot) ?: $appRoot;
        $this->cacheDir = $this->appRoot . '/' . self::CACHE_DIRECTORY;
        $this->clock = $clock ?? time(...);
    }

    /**
     * @param string|null           $container defaults to this host's name
     * @param (Closure(): int)|null $clock     the current Unix time, by default time()
     */
    public static function forApplication(
        string $appRoot,
        int $rescanAfterSeconds = self::DEFAULT_RESCAN_AFTER_SECONDS,
        ?string $container = null,
        ?Closure $clock = null,
    ): self {
        return new self($appRoot, $container ?? (gethostname() ?: ''), $rescanAfterSeconds, $clock);
    }

    public function key(): string
    {
        $this->manifest = $this->currentManifest();
        $key = $this->keyFor($this->manifest['sources']);

        if (!is_file($this->cacheFile(self::CONFIG_CACHE, $key))) {
            $this->deleteCaches(except: $key);
        }

        return $key;
    }

    /**
     * Whether Laminas will be able to write the caches named after the key. It fails the request when
     * it can't, which happens when the bind-mounted cache directory belongs to another user.
     *
     * @param string $key as returned by key()
     */
    public function canWriteCachesFor(string $key): bool
    {
        if (!is_dir($this->cacheDir) || !is_writable($this->cacheDir)) {
            return false;
        }

        // Laminas only writes the caches that aren't there yet
        if (
            is_file($this->cacheFile(self::CONFIG_CACHE, $key))
            && is_file($this->cacheFile(self::MODULE_MAP_CACHE, $key))
        ) {
            return true;
        }

        // is_writable() isn't enough before a write: through Docker Desktop's file sharing it reports a
        // directory as writable that the host then refuses to write to
        $probe = $this->manifestPath() . '.' . getmypid() . '.probe';

        if (@file_put_contents($probe, '') === false) {
            return false;
        }

        @unlink($probe);

        return true;
    }

    /**
     * Where Laminas keeps one of its caches for the key
     */
    private function cacheFile(string $cache, string $key): string
    {
        return $this->cacheDir . '/' . $cache . '.' . $key . '.php';
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

        // The files found by scanning, and those an earlier merge loaded, were checked when the key was
        // worked out, before this merge read them, so they keep those signatures: one changed while the
        // configuration was being merged then changes the key on the next request. A file no earlier
        // merge loaded can only be checked now.
        $manifest = $this->manifest ?? $this->readManifest();
        $files = $manifest['files'] ?? $this->scan();
        $loaded = array_diff_key(
            $this->signatures(array_filter($includedFiles, $this->isApplicationFile(...))),
            $files
        );
        $loaded = array_replace($loaded, array_intersect_key($manifest['loaded'] ?? [], $loaded));

        $this->manifest = $this->saveManifest($files, $loaded, $manifest['scannedAt'] ?? $this->now());

        return $this->keyFor($this->manifest['sources']);
    }

    private function keyFor(string $sourcesHash): string
    {
        return self::KEY_PREFIX . substr(hash('xxh128', $sourcesHash . "\0" . $this->container), 0, 16);
    }

    /**
     * The files that build the configuration, from the last scan if it's still current
     *
     * @return Manifest
     */
    private function currentManifest(): array
    {
        clearstatcache();

        $manifest = $this->readManifest();

        if (
            $manifest !== null
            && !$this->isScanDue($manifest['scannedAt'])
            && $this->areUnchanged($manifest['files'] + $manifest['loaded'])
        ) {
            return $manifest;
        }

        return $this->saveManifest(
            $this->scan(),
            $this->signatures(array_keys($manifest['loaded'] ?? [])),
            $this->now()
        );
    }

    /**
     * @param array<string, string> $files  path => signature of the files found by scanning
     * @param array<string, string> $loaded path => signature of the other files loaded by the last merge
     *
     * @return Manifest
     */
    private function saveManifest(array $files, array $loaded, int $scannedAt): array
    {
        $sources = $files + $loaded;
        ksort($sources);

        $manifest = [
            'sources' => hash('xxh128', json_encode($sources, JSON_THROW_ON_ERROR)),
            'scannedAt' => $scannedAt,
            'files' => $files,
            'loaded' => $loaded,
        ];

        $this->writeManifest($manifest);

        return $manifest;
    }

    /**
     * @return Manifest|null
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

        /** @var Manifest $manifest */
        return $manifest;
    }

    private function isScanDue(int $scannedAt): bool
    {
        return $this->now() - $scannedAt >= $this->rescanAfterSeconds;
    }

    private function now(): int
    {
        return ($this->clock)();
    }

    /**
     * @param array<string, string> $files path => signature, as recorded
     */
    private function areUnchanged(array $files): bool
    {
        foreach ($files as $path => $signature) {
            if (!$this->matches($path, $signature)) {
                return false;
            }
        }

        return true;
    }

    private function matches(string $path, string $signature): bool
    {
        $stat = @stat($path);

        if ($stat === false) {
            return false;
        }

        $parts = explode(':', $signature, 3);

        return $parts[0] === (string) $stat['mtime']
            && ($parts[1] ?? null) === (string) $stat['size']
            && (!isset($parts[2]) || @hash_file('xxh128', $path) === $parts[2]);
    }

    /**
     * @return array<string, string> path => signature of every file found in the configuration's sources
     */
    private function scan(): array
    {
        $paths = [];

        foreach ($this->directoryPatterns() as $pattern) {
            foreach (glob($pattern, GLOB_ONLYDIR) ?: [] as $directory) {
                $paths = array_merge($paths, $this->configurationFilesIn($directory));
            }
        }

        foreach ($this->filePatterns() as $pattern) {
            $paths = array_merge($paths, glob($pattern) ?: []);
        }

        // The libraries are linked into vendor/, so resolve the links: a file the merge loads is then
        // recorded under the same path as when it's found by scanning
        $realPaths = array_map(static fn (string $path): string => realpath($path) ?: $path, $paths);

        return $this->signatures(array_unique($realPaths));
    }

    /**
     * The configuration files in a directory, at any depth. A directory that can't be read, or that
     * git checkout or composer install removes while it's being listed, is left out rather than
     * failing the request: the next scan finds whatever is there by then.
     *
     * @return string[]
     */
    private function configurationFilesIn(string $directory): array
    {
        $paths = [];

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY,
                RecursiveIteratorIterator::CATCH_GET_CHILD
            );

            /** @var SplFileInfo $file */
            foreach ($iterator as $file) {
                if ($file->isFile() && $this->isConfigurationFile($file)) {
                    $paths[] = $file->getPathname();
                }
            }
        } catch (RuntimeException) {
            // Keep the files found so far
        }

        return $paths;
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
        $recentSince = $this->now() - self::RECENT_CHANGE_SECONDS;

        foreach ($paths as $path) {
            $signature = $this->signature($path, $recentSince);

            if ($signature !== null) {
                $signatures[$path] = $signature;
            }
        }

        ksort($signatures);

        return $signatures;
    }

    /**
     * The file's modification time and size, then a hash of its contents if it changed recently
     */
    private function signature(string $path, int $recentSince): ?string
    {
        $stat = @stat($path);

        if ($stat === false) {
            return null;
        }

        $signature = $stat['mtime'] . ':' . $stat['size'];

        if ($stat['mtime'] < $recentSince) {
            return $signature;
        }

        $contentsHash = @hash_file('xxh128', $path);

        return $contentsHash === false ? $signature : $signature . ':' . $contentsHash;
    }

    /**
     * @param Manifest $manifest
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

    private function deleteCaches(string $except): void
    {
        foreach (glob($this->cacheDir . '/module-*.' . self::KEY_PREFIX . '*.php') ?: [] as $cacheFile) {
            if (str_ends_with($cacheFile, '.' . $except . '.php')) {
                continue;
            }

            // OPcache keeps the compiled copy of a deleted file, which is a few megabytes for a
            // configuration cache, and never counts it as wasted. Every change to the configuration
            // leaves one behind, so they would fill OPcache's memory, which then stops caching.
            if (function_exists('opcache_invalidate')) {
                @opcache_invalidate($cacheFile, true);
            }

            @unlink($cacheFile);
        }
    }

    private function manifestPath(): string
    {
        return $this->cacheDir . '/' . self::MANIFEST_FILE;
    }
}
