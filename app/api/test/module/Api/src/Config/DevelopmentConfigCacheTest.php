<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Config;

use Dvsa\Olcs\Utils\Development\ConfigCacheKey;
use Dvsa\Olcs\Utils\Development\DevelopmentConfig;
use FilesystemIterator;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayUtils;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Boots Laminas's module manager with the development configuration, the way a request in the local
 * environment does, to check the configuration cache against the real thing: that Laminas writes it
 * under the name the next request looks for, reads it back, and builds it again when a file it was
 * built from changes. The library's own tests can't, because it doesn't depend on Laminas's module
 * manager and so only has stand-ins for it.
 *
 * The application here is a small one in a temporary directory, with one module whose configuration
 * also loads a file from outside its config directory, as the real ones load classes.
 */
class DevelopmentConfigCacheTest extends TestCase
{
    private const string MODULE_CONFIG = 'module/Example/config/module.config.php';
    private const string LOADED_WHILE_MERGING = 'module/Example/src/loaded-while-merging.php';

    private string $appRoot;
    private string $cacheDirectory;
    private string|false $configCacheSetting;

    // How many times the module's configuration has been asked for, which Laminas only does when it
    // merges the configuration
    private int $merges = 0;

    protected function setUp(): void
    {
        // get_included_files() reports real paths, and the temporary directory can be a symlink
        $this->appRoot = realpath(sys_get_temp_dir()) . '/development-config-cache-' . bin2hex(random_bytes(6));
        $this->cacheDirectory = $this->appRoot . '/data/cache';

        mkdir($this->appRoot . '/config/autoload', 0777, true);
        mkdir($this->cacheDirectory, 0777, true);
        $this->writeFile(self::MODULE_CONFIG, $this->moduleConfig('first'));
        $this->writeFile(self::LOADED_WHILE_MERGING, "<?php return 'first';");

        $this->configCacheSetting = getenv(DevelopmentConfig::CONFIG_CACHE_SETTING);
        putenv(DevelopmentConfig::CONFIG_CACHE_SETTING . '=1');
        ini_set('error_log', $this->appRoot . '/error.log');
    }

    protected function tearDown(): void
    {
        ini_restore('error_log');
        putenv(
            $this->configCacheSetting === false
                ? DevelopmentConfig::CONFIG_CACHE_SETTING
                : DevelopmentConfig::CONFIG_CACHE_SETTING . '=' . $this->configCacheSetting
        );
        chmod($this->cacheDirectory, 0777);

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->appRoot, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }

        rmdir($this->appRoot);
    }

    public function testLaminasWritesTheCacheUnderTheKeyTheNextRequestWorksOutAndThenReadsIt(): void
    {
        $config = $this->boot();

        $this->assertSame(['value' => 'first', 'loaded' => 'first'], $config['example']);
        $this->assertSame(1, $this->merges);

        // The key changed while the configuration was merged, because the merge loaded a file the scan
        // of the config directories doesn't find. The cache has to be written under the new key.
        $cacheFiles = $this->cacheFiles('module-config-cache.');
        $this->assertCount(1, $cacheFiles);
        $nextKey = ConfigCacheKey::forApplication($this->appRoot)->key();
        $this->assertSame(["module-config-cache.$nextKey.php"], $cacheFiles);

        $this->assertSame($config['example'], $this->boot()['example']);
        $this->assertSame(1, $this->merges, 'The next request reads the cache rather than merging again');
        $this->assertSame($cacheFiles, $this->cacheFiles('module-config-cache.'));
    }

    public function testChangingAConfigFileBuildsTheCacheAgain(): void
    {
        $this->boot();

        $this->writeFile(self::MODULE_CONFIG, $this->moduleConfig('second'), hoursOld: 1);
        $config = $this->boot();

        $this->assertSame(['value' => 'second', 'loaded' => 'first'], $config['example']);
        $this->assertSame(2, $this->merges);
        $this->assertCount(1, $this->cacheFiles('module-config-cache.'), 'The cache it replaces is deleted');

        $this->boot();
        $this->assertSame(2, $this->merges, 'The request after that reads the new cache');
    }

    public function testChangingAFileTheConfigurationLoadedWhileItWasMergedBuildsTheCacheAgain(): void
    {
        $this->boot();

        $this->writeFile(self::LOADED_WHILE_MERGING, "<?php return 'second';", hoursOld: 1);
        $config = $this->boot();

        $this->assertSame(['value' => 'first', 'loaded' => 'second'], $config['example']);
        $this->assertSame(2, $this->merges);
        $this->assertCount(1, $this->cacheFiles('module-config-cache.'), 'The cache it replaces is deleted');

        $this->boot();
        $this->assertSame(2, $this->merges, 'The request after that reads the new cache');
    }

    public function testTheApplicationStillBootsWhenTheCacheDirectoryCannotBeWrittenTo(): void
    {
        chmod($this->cacheDirectory, 0555);

        // Root can write to it whatever its permissions, and then the cache is used as usual
        $cacheFilesExpected = is_writable($this->cacheDirectory) ? 1 : 0;

        $this->assertSame('first', $this->boot()['example']['value']);
        $this->assertSame('first', $this->boot()['example']['value']);
        $this->assertCount($cacheFilesExpected, $this->cacheFiles('module-config-cache.'));
        $this->assertSame(2 - $cacheFilesExpected, $this->merges);
    }

    /**
     * Loads the application's modules as a web request does and returns the merged configuration
     *
     * @return array<string, mixed>
     */
    private function boot(): array
    {
        $appConfig = ArrayUtils::merge(
            [
                'modules' => ['Example' => $this->exampleModule()],
                'module_listener_options' => ['module_paths' => [], 'cache_dir' => $this->cacheDirectory],
            ],
            DevelopmentConfig::forApplication($this->appRoot, sapi: 'fpm-fcgi')
        );

        $serviceManager = new ServiceManager();
        (new ServiceManagerConfig())->configureServiceManager($serviceManager);
        $serviceManager->setService('ApplicationConfig', $appConfig);

        $moduleManager = $serviceManager->get('ModuleManager');
        assert($moduleManager instanceof ModuleManager);
        $moduleManager->loadModules();

        $config = $serviceManager->get('config');
        assert(is_array($config));

        return $config;
    }

    /**
     * A module's configuration, which also loads a file from outside the module's config directory
     */
    private function moduleConfig(string $value): string
    {
        $loaded = "require __DIR__ . '/../src/loaded-while-merging.php'";

        return "<?php return ['example' => ['value' => '$value', 'loaded' => $loaded]];";
    }

    private function exampleModule(): object
    {
        $configFile = $this->appRoot . '/' . self::MODULE_CONFIG;
        $countMerge = function (): void {
            $this->merges++;
        };

        return new class ($configFile, $countMerge) {
            public function __construct(private readonly string $configFile, private readonly \Closure $countMerge)
            {
            }

            /**
             * @return array<string, mixed>
             */
            public function getConfig(): array
            {
                ($this->countMerge)();

                return require $this->configFile;
            }
        };
    }

    /**
     * @return string[] the names of the cache files that start with the prefix
     */
    private function cacheFiles(string $prefix): array
    {
        return array_map(basename(...), glob($this->cacheDirectory . '/' . $prefix . '*.php') ?: []);
    }

    /**
     * Files are written as hours old, because one changed in the last couple of seconds is checked by
     * its contents as well, and its key changes again once it's older
     */
    private function writeFile(string $relativePath, string $contents, int $hoursOld = 2): void
    {
        $path = $this->appRoot . '/' . $relativePath;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        file_put_contents($path, $contents);
        touch($path, time() - $hoursOld * 3600);
    }
}
