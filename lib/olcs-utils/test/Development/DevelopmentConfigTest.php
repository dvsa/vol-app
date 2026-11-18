<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Utils\Development;

use Dvsa\Olcs\Utils\Development\ConfigCacheKey;
use Dvsa\Olcs\Utils\Development\ConfigCacheModule;
use Dvsa\Olcs\Utils\Development\DevelopmentConfig;
use FilesystemIterator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class DevelopmentConfigTest extends TestCase
{
    private const string WEB_SAPI = 'fpm-fcgi';
    private const array TOOLBAR_MODULES = ['Laminas\DeveloperTools', 'SanSessionToolbar'];

    private string $appRoot;
    private string $cacheDirectory;
    private string $errorLog;

    /** @var array<string, string|false> the settings as they were before the test */
    private array $settingsBefore;

    protected function setUp(): void
    {
        $this->settingsBefore = [
            DevelopmentConfig::CONFIG_CACHE_SETTING => getenv(DevelopmentConfig::CONFIG_CACHE_SETTING),
            DevelopmentConfig::DEVELOPER_TOOLS_SETTING => getenv(DevelopmentConfig::DEVELOPER_TOOLS_SETTING),
        ];

        $this->appRoot = realpath(sys_get_temp_dir()) . '/development-config-' . bin2hex(random_bytes(6));
        $this->cacheDirectory = $this->appRoot . '/data/cache';
        $this->errorLog = $this->appRoot . '/error.log';

        mkdir($this->appRoot . '/config/autoload', 0777, true);
        mkdir($this->cacheDirectory, 0777, true);
        file_put_contents($this->appRoot . '/config/application.config.php', '<?php return [];');

        ini_set('error_log', $this->errorLog);
        $this->setSettings(configCache: null, developerTools: null);
    }

    protected function tearDown(): void
    {
        ini_restore('error_log');

        foreach ($this->settingsBefore as $name => $value) {
            putenv($value === false ? $name : $name . '=' . $value);
        }

        if (is_dir($this->cacheDirectory)) {
            chmod($this->cacheDirectory, 0777);
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->appRoot, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }

        rmdir($this->appRoot);
    }

    public function testTheConfigurationIsCachedUnderTheConfigCacheKeyWhenItsSettingIsOn(): void
    {
        $this->setSettings(configCache: '1');

        $config = DevelopmentConfig::forApplication($this->appRoot, sapi: self::WEB_SAPI);
        $options = $config['module_listener_options'];

        $this->assertTrue($options['config_cache_enabled']);
        $this->assertTrue($options['module_map_cache_enabled']);
        $this->assertSame(ConfigCacheKey::forApplication($this->appRoot)->key(), $options['config_cache_key']);
        $this->assertSame($options['config_cache_key'], $options['module_map_cache_key']);
        $this->assertSame([ConfigCacheKey::MODULE_NAME], array_keys($config['modules']));
        $this->assertInstanceOf(ConfigCacheModule::class, $config['modules'][ConfigCacheKey::MODULE_NAME]);
        $this->assertFileDoesNotExist($this->errorLog);
    }

    /**
     * @return array<string, array{string|null}>
     */
    public static function settingsThatAreOff(): array
    {
        return [
            'not set' => [null],
            'empty' => [''],
            'zero' => ['0'],
            'false' => ['false'],
            'off' => ['off'],
        ];
    }

    #[DataProvider('settingsThatAreOff')]
    public function testTheConfigurationIsNotCachedWhenItsSettingIsOff(?string $setting): void
    {
        $this->setSettings(configCache: $setting);

        $config = DevelopmentConfig::forApplication($this->appRoot, sapi: self::WEB_SAPI);

        $this->assertCacheIsOff($config);
        $this->assertFileDoesNotExist($this->errorLog, 'Turning the cache off is not worth a log line');
    }

    public function testCommandLineRunsDoNotCacheTheConfiguration(): void
    {
        $this->setSettings(configCache: '1');

        $this->assertCacheIsOff(DevelopmentConfig::forApplication($this->appRoot, sapi: 'cli'));
        $this->assertCacheIsOff(DevelopmentConfig::forApplication($this->appRoot), 'The tests run on the command line');
    }

    public function testTheConfigurationIsNotCachedWhenThereIsNoCacheDirectory(): void
    {
        $this->setSettings(configCache: '1');
        rmdir($this->cacheDirectory);

        $config = DevelopmentConfig::forApplication($this->appRoot, sapi: self::WEB_SAPI);

        $this->assertCacheIsOff($config);
        $this->assertStringContainsString($this->cacheDirectory, (string) file_get_contents($this->errorLog));
    }

    public function testTheConfigurationIsNotCachedWhenTheCacheDirectoryCannotBeWrittenTo(): void
    {
        $this->setSettings(configCache: '1');
        chmod($this->cacheDirectory, 0555);

        // Root can write to it whatever its permissions, and then the cache stays on
        $canWrite = is_writable($this->cacheDirectory);

        $config = DevelopmentConfig::forApplication($this->appRoot, sapi: self::WEB_SAPI);

        $this->assertSame($canWrite, $config['module_listener_options']['config_cache_enabled']);
        $this->assertSame($canWrite, $config['module_listener_options']['module_map_cache_enabled']);
        $this->assertSame($canWrite, isset($config['modules'][ConfigCacheKey::MODULE_NAME]));
        $this->assertSame($canWrite, !file_exists($this->errorLog), 'Why the cache is off is logged');
    }

    public function testDeveloperToolModulesAreLoadedOnlyWhenTheirSettingIsOn(): void
    {
        $this->setSettings(developerTools: '1');
        $this->assertSame(
            self::TOOLBAR_MODULES,
            DevelopmentConfig::forApplication($this->appRoot, self::TOOLBAR_MODULES)['modules']
        );

        $this->setSettings(developerTools: '0');
        $this->assertSame([], DevelopmentConfig::forApplication($this->appRoot, self::TOOLBAR_MODULES)['modules']);

        $this->setSettings(developerTools: null);
        $this->assertSame([], DevelopmentConfig::forApplication($this->appRoot, self::TOOLBAR_MODULES)['modules']);
    }

    public function testDeveloperToolModulesAreLoadedBeforeTheConfigCacheModule(): void
    {
        $this->setSettings(configCache: '1', developerTools: '1');

        $config = DevelopmentConfig::forApplication($this->appRoot, self::TOOLBAR_MODULES, self::WEB_SAPI);

        $this->assertSame([0, 1, ConfigCacheKey::MODULE_NAME], array_keys($config['modules']));
        $this->assertSame(self::TOOLBAR_MODULES, array_slice($config['modules'], 0, 2));
    }

    public function testDevelopmentConfigFilesAreLoadedFromTheApplicationsAutoloadDirectory(): void
    {
        $config = DevelopmentConfig::forApplication($this->appRoot);

        $this->assertSame(
            [$this->appRoot . '/config/autoload/{,*.}{global,local}-development.php'],
            $config['module_listener_options']['config_glob_paths']
        );
    }

    /**
     * @param array{modules: array<array-key, mixed>, module_listener_options: array<string, mixed>} $config
     */
    private function assertCacheIsOff(array $config, string $message = ''): void
    {
        $this->assertFalse($config['module_listener_options']['config_cache_enabled'], $message);
        $this->assertFalse($config['module_listener_options']['module_map_cache_enabled'], $message);
        $this->assertSame([], $config['modules'], $message);
    }

    /**
     * Sets the environment variables compose.yaml passes to the containers; null leaves one unset
     */
    private function setSettings(?string $configCache = null, ?string $developerTools = null): void
    {
        foreach (
            [
                DevelopmentConfig::CONFIG_CACHE_SETTING => $configCache,
                DevelopmentConfig::DEVELOPER_TOOLS_SETTING => $developerTools,
            ] as $name => $value
        ) {
            putenv($value === null ? $name : $name . '=' . $value);
        }
    }
}
