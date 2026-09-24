<?php

declare(strict_types=1);

namespace OlcsTest\Config;

use Dvsa\LaminasConfigCloudParameters\Exception\ParameterNotFoundException;
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
 * modules sets static state (e.g. Common\Module's date formats) that would leak into other tests.
 */
#[RunTestsInSeparateProcesses]
class ConfigParametersTest extends TestCase
{
    /**
     * Every placeholder the config expects Parameter Store / Secrets Manager to supply. When this changes, the
     * parameter has to be added to (or can be removed from) every environment too.
     */
    private const array EXPECTED_PARAMETERS = [
        'assets_cache_busting_strategy',
        'cache_encryption_secret_shared',
        'cache_encryption_secret_ss',
        'cqrs_cache_enabled',
        'cqrs_cache_long_ttl',
        'cqrs_cache_medium_ttl',
        'domain',
        'log_level',
        'olcs_google_gtm_auth',
        'olcs_google_gtm_preview',
        'olcs_google_id',
        'olcs_ss_cookie',
        'redis_cache_fqdn',
        'shd_proxy',
        'user_unique_id_salt',
        'verify_forwarder_valid_origin',
    ];

    public function testConfigReferencesExactlyTheExpectedParameters(): void
    {
        StubParameterProvider::$parameters = [];

        try {
            $this->loadConfig();
            $names = [];
        } catch (ParameterNotFoundException $e) {
            $names = $e->getUnresolvedParameterNames();
        }

        sort($names);

        $this->assertSame(
            self::EXPECTED_PARAMETERS,
            $names,
            'The placeholders in the config have changed: update EXPECTED_PARAMETERS, and every environment\'s parameters'
        );
    }

    public function testMergeListenerResolvesEveryPlaceholder(): void
    {
        StubParameterProvider::$parameters = self::EXPECTED_PARAMETERS;

        $config = $this->loadConfig();

        $json = json_encode($config, JSON_PARTIAL_OUTPUT_ON_ERROR | JSON_UNESCAPED_SLASHES);
        $this->assertIsString($json);
        $this->assertDoesNotMatchRegularExpression('/%[\w.-]+%/', $json);

        // The Boolean cast on [query_cache][enabled] ran
        $this->assertTrue($config['query_cache']['enabled']);
    }

    /**
     * @return array<string, mixed>
     */
    private function loadConfig(): array
    {
        $configDir = dirname(__DIR__, 4) . '/config';

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
