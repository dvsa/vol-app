<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Config;

use Laminas\ModuleManager\ModuleEvent;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;

/**
 * Loads the app's modules the way it boots, so dvsa/laminas-config-cloud-parameters' merge listener runs over the
 * real merged config. Nothing else in the suite runs it, which left upgrades of that library and the Symfony
 * components it resolves placeholders with unguarded.
 *
 * Only *.global.php is read, so a developer's local.php can't change the outcome, and whatever providers the config
 * names are swapped for a stub before the listener runs, so nothing reaches AWS. Separate processes because loading
 * modules can set static state that would leak into other tests.
 */
#[RunTestsInSeparateProcesses]
class ConfigParametersTest extends TestCase
{
    public function testMergeListenerResolvesEveryPlaceholder(): void
    {
        $config = $this->loadConfig();

        // Would pass vacuously if the stub were never asked, or the config had nothing to resolve
        $this->assertNotEmpty(StubParameterProvider::$supplied);

        // Only the names that were supplied: escaped %% comes back as a literal %, so e.g. '%%s/olcs/%%s' would look
        // like a placeholder if the resolved config were scanned blind
        $unresolved = array_values(array_intersect(StubParameterProvider::$supplied, StubParameterProvider::placeholdersIn($config)));
        $this->assertSame([], $unresolved, 'Placeholders left unresolved');
    }

    /**
     * @return array<string, mixed>
     */
    private function loadConfig(): array
    {
        $configDir = dirname(__DIR__, 5) . '/config';

        $appConfig = require $configDir . '/application.config.php';
        $appConfig['module_listener_options'] = array_merge($appConfig['module_listener_options'], [
            'config_glob_paths' => [$configDir . '/autoload/{,*.}global.php'],
            'config_cache_enabled' => false,
            'module_map_cache_enabled' => false,
        ]);

        $serviceManager = new ServiceManager();
        (new ServiceManagerConfig($appConfig['service_manager'] ?? []))->configureServiceManager($serviceManager);
        $serviceManager->setService('ApplicationConfig', $appConfig);

        $moduleManager = $serviceManager->get('ModuleManager');
        assert($moduleManager instanceof ModuleManager);

        // After the config files are merged (priority 1000), before the config-parameters listener runs (priority 1)
        $moduleManager->getEventManager()->attach(ModuleEvent::EVENT_MERGE_CONFIG, function (ModuleEvent $e): void {
            $configListener = $e->getConfigListener();
            assert($configListener !== null);
            $config = $configListener->getMergedConfig(false);
            $config['config_parameters']['providers'] = [StubParameterProvider::class => ['stub']];
            $configListener->setMergedConfig($config);
        }, 500);

        $moduleManager->loadModules();

        $config = $serviceManager->get('config');
        assert(is_array($config));

        return $config;
    }
}
