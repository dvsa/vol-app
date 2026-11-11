<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Utils\Development;

/**
 * Builds the configuration that an application's config/development.config.php returns. Every
 * developer has their own copy of that file, made from development.config.php.dist, so the file
 * only calls this, and a change made here reaches every copy.
 *
 * The settings are environment variables that compose.yaml passes in from the root .env file. See
 * docs/app/local-setup.
 *
 * Not for production, which has no development.config.php.
 */
final class DevelopmentConfig
{
    public const string CONFIG_CACHE_SETTING = 'VOL_CONFIG_CACHE';
    public const string DEVELOPER_TOOLS_SETTING = 'VOL_DEVTOOLS';

    /**
     * @param string   $appRoot              the application's root directory
     * @param string[] $developerToolModules modules that slow every request down, such as the developer
     *                                       toolbar, so they only load when VOL_DEVTOOLS is on
     * @param string   $sapi                 how PHP is running
     *
     * @return array{modules: array<array-key, mixed>, module_listener_options: array<string, mixed>}
     */
    public static function forApplication(
        string $appRoot,
        array $developerToolModules = [],
        string $sapi = PHP_SAPI,
    ): array {
        $appRoot = realpath($appRoot) ?: $appRoot;
        $configCache = self::configCache($appRoot, $sapi);
        $cacheKey = $configCache?->key();

        if ($configCache !== null && $cacheKey !== null && !$configCache->canWriteCachesFor($cacheKey)) {
            // Laminas fails the request when it can't write the cache, so the application runs without it
            error_log(sprintf(
                'The Laminas configuration cache is off, which slows every request down, because PHP cannot'
                . ' write to %s. See "Development settings" in docs/app/local-setup.',
                $appRoot . '/' . ConfigCacheKey::CACHE_DIRECTORY
            ));
            $configCache = $cacheKey = null;
        }

        return [
            // Additional modules to load in development. The config cache module records the files
            // loaded while the configuration is merged.
            'modules' => array_merge(
                self::isOn(self::DEVELOPER_TOOLS_SETTING) ? array_values($developerToolModules) : [],
                $configCache === null ? [] : [ConfigCacheKey::MODULE_NAME => $configCache->module()],
            ),
            // Configuration overrides in development
            'module_listener_options' => [
                'config_glob_paths' => [$appRoot . '/config/autoload/{,*.}{global,local}-development.php'],
                'config_cache_enabled' => $configCache !== null,
                'module_map_cache_enabled' => $configCache !== null,
                'config_cache_key' => $cacheKey ?? 'development',
                'module_map_cache_key' => $cacheKey ?? 'development',
            ],
        ];
    }

    /**
     * Merging the configuration is the slowest part of a request, so the result is cached. The cache
     * is named after what the configuration is built from (its files, the classes it loads while
     * it's merged, and the container), so it's rebuilt when any of them changes; see ConfigCacheKey.
     */
    private static function configCache(string $appRoot, string $sapi): ?ConfigCacheKey
    {
        // Command-line runs don't cache: container-cli.php gives them fixed cache names
        if ($sapi === 'cli' || !self::isOn(self::CONFIG_CACHE_SETTING)) {
            return null;
        }

        return ConfigCacheKey::forApplication($appRoot);
    }

    private static function isOn(string $setting): bool
    {
        return filter_var(getenv($setting), FILTER_VALIDATE_BOOLEAN);
    }
}
