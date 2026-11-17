<?php

declare(strict_types=1);

namespace OlcsTest\Config;

use Dvsa\Olcs\Utils\Development\DevelopmentConfig;
use PHPUnit\Framework\TestCase;

/**
 * Runs config/development.config.php.dist, which nothing else does: each developer copies it to
 * development.config.php, which Git ignores.
 */
class DevelopmentConfigDistTest extends TestCase
{
    private string $configDirectory;
    private string|false $developerToolsSetting;

    protected function setUp(): void
    {
        $this->configDirectory = dirname(__DIR__, 4) . '/config';
        $this->developerToolsSetting = getenv(DevelopmentConfig::DEVELOPER_TOOLS_SETTING);
    }

    protected function tearDown(): void
    {
        $this->setDeveloperTools($this->developerToolsSetting === false ? null : $this->developerToolsSetting);
    }

    public function testTheDeveloperToolbarModulesAreLoadedWhenTheirSettingIsOn(): void
    {
        $this->setDeveloperTools('1');

        $modules = $this->distConfig()['modules'];

        $this->assertSame(['Laminas\DeveloperTools', 'SanSessionToolbar'], $modules);

        foreach ($modules as $module) {
            $this->assertTrue(class_exists($module . '\Module'), "The $module module isn't installed");
        }
    }

    public function testNoDevelopmentModulesAreLoadedWhenTheToolbarIsOff(): void
    {
        // Tests run on the command line, which doesn't cache the configuration, so the module that
        // records what the cache is built from isn't loaded either
        $this->setDeveloperTools('0');
        $this->assertSame([], $this->distConfig()['modules']);

        $this->setDeveloperTools(null);
        $this->assertSame([], $this->distConfig()['modules']);
    }

    public function testDevelopmentConfigFilesAreLoadedFromTheAutoloadDirectory(): void
    {
        $options = $this->distConfig()['module_listener_options'];

        $this->assertFalse($options['config_cache_enabled']);
        $this->assertSame(
            [realpath($this->configDirectory) . '/autoload/{,*.}{global,local}-development.php'],
            $options['config_glob_paths']
        );
    }

    /**
     * @return array{modules: array<array-key, mixed>, module_listener_options: array<string, mixed>}
     */
    private function distConfig(): array
    {
        return require $this->configDirectory . '/development.config.php.dist';
    }

    private function setDeveloperTools(?string $value): void
    {
        $name = DevelopmentConfig::DEVELOPER_TOOLS_SETTING;

        putenv($value === null ? $name : $name . '=' . $value);
    }
}
