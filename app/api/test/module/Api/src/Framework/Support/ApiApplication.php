<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Framework\Support;

use Dvsa\OlcsTest\Api\Config\StubParameterProvider;
use Dvsa\OlcsTest\Support\DoctrineMetadata;
use Laminas\ModuleManager\ModuleEvent;
use Laminas\ModuleManager\ModuleManager;
use Laminas\Mvc\Application;
use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;
use Laminas\Stdlib\ArrayUtils;
use Monolog\Handler\TestHandler;
use Monolog\Logger as MonologLogger;
use Dvsa\Olcs\Api\Service\Toggle\ToggleService;
use Dvsa\Olcs\Api\Service\Translator\TranslationLoader;
use Laminas\I18n\Translator\LoaderPluginManager;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

/**
 * The real api — every module, every config file, every onBootstrap — with only its infrastructure swapped
 * for in-memory stand-ins, so it boots in the unit suite with no AWS, database, Redis or log directory.
 *
 * This is the one place that knows how the api is assembled, and it is the seam the laminas-mvc removal
 * moves. Today config/container.php and config/container-cli.php both call
 * Laminas\Mvc\Application::init(), which (1) loads the modules and merges their config, then
 * (2) bootstraps, running every module's onBootstrap. This class repeats those two steps with test
 * infrastructure slotted in between them. When the container files stop calling Application::init(),
 * change this class to build the application the new way and every Framework test runs against the new
 * bootstrap unchanged. A test that then fails has found a behaviour the new bootstrap dropped.
 *
 * What is swapped, and why:
 *  - config parameters: StubParameterProvider supplies '1' for every %placeholder% (no AWS)
 *  - ENVIRONMENT_NAME: hidden while the config files load, as in an environment-less process
 *  - local and development config: not loaded (per-developer, absent in CI); global config only
 *  - config and module-map caches: off, so the files on disk are what is tested
 *  - the encryption key: a fixed 32-byte key, as AES needs and the stubbed '1' is not
 *  - Doctrine: serverVersion pinned, so DBAL never opens a connection; the connection points at a socket
 *    that does not exist, so anything that does query fails at once rather than after a DNS lookup
 *  - the Redis-backed caches: in-memory; the Redis connection is an unconnected \Redis
 *  - log writers: the real Logger, built from the real config, with its handlers replaced by one
 *    TestHandler (the configured file, /var/log/dvsa, does not exist in a test)
 *  - feature toggles: fixed answers (the Address module reads one in onBootstrap)
 *  - translations: none (they live in Redis and the database), so messages translate to their keys
 *
 * Booting changes process-wide state; FrameworkTestCase captures and restores it around every test.
 */
final class ApiApplication
{
    /**
     * Exactly 32 bytes: phpseclib's AES accepts 16, 24 or 32. A fixed key keeps ciphertext assertable.
     */
    public const string ENCRYPTION_KEY = 'framework-test-encryption-key-32';

    /**
     * Shared for the life of the process: Doctrine caches parsed entity metadata here, and parsing it
     * for every boot would cost ~200ms a time. Metadata never differs between boots, so sharing is safe.
     */
    private static ?ArrayAdapter $doctrineCache = null;

    private function __construct(
        public readonly ServiceManager $serviceManager,
        public readonly Application $application,
        public readonly TestHandler $logs,
    ) {
    }

    /**
     * The application config the matching container file starts from.
     *
     * config/container.php: config/application.config.php (plus development.config.php, if a developer has
     * one). config/container-cli.php: the same, plus the Dvsa\Olcs\Cli module and its own cache keys.
     *
     * @return array<string, mixed>
     */
    public static function applicationConfig(Entrypoint $entrypoint): array
    {
        $configDir = self::root() . '/config';

        $appConfig = require $configDir . '/application.config.php';
        assert(is_array($appConfig));

        if ($entrypoint === Entrypoint::Cli) {
            $appConfig = ArrayUtils::merge($appConfig, ['modules' => ['Dvsa\Olcs\Cli']]);
        }

        $appConfig['module_listener_options'] = array_merge($appConfig['module_listener_options'], [
            'config_glob_paths' => [$configDir . '/autoload/{,*.}global.php'],
            'config_cache_enabled' => false,
            'module_map_cache_enabled' => false,
        ]);

        return $appConfig;
    }

    /**
     * Step 1 of Application::init(): load the modules and merge their config. Nothing is bootstrapped.
     *
     * Without test infrastructure the merged config is exactly what production builds, apart from the
     * stubbed parameter values — use that to inspect configuration. With it, the container can also
     * create services: the overrides that keep Doctrine and the encrypter working offline are applied.
     */
    public static function loadModules(Entrypoint $entrypoint, bool $withTestInfrastructure = true): ServiceManager
    {
        $appConfig = self::applicationConfig($entrypoint);

        $serviceManager = new ServiceManager();
        (new ServiceManagerConfig($appConfig['service_manager'] ?? []))->configureServiceManager($serviceManager);
        $serviceManager->setService('ApplicationConfig', $appConfig);

        $moduleManager = $serviceManager->get('ModuleManager');
        assert($moduleManager instanceof ModuleManager);
        $events = $moduleManager->getEventManager();

        // After the config files are merged (priority 1000), before config-cloud-parameters resolves (priority 1)
        $events->attach(ModuleEvent::EVENT_MERGE_CONFIG, static function (ModuleEvent $event): void {
            self::changeMergedConfig($event, static function (array $config): array {
                $config['config_parameters']['providers'] = [StubParameterProvider::class => ['stub']];

                return $config;
            });
        }, 500);

        if ($withTestInfrastructure) {
            // After the placeholders are resolved, so these values are final
            $events->attach(ModuleEvent::EVENT_MERGE_CONFIG, static function (ModuleEvent $event): void {
                self::changeMergedConfig($event, self::testInfrastructureConfig(...));
            }, -1000);
        }

        // The config files branch on ENVIRONMENT_NAME (AWS parameter sources, production-only settings).
        // Load them as an environment-less process would, whatever the shell running the tests has set.
        $environmentName = getenv('ENVIRONMENT_NAME');
        putenv('ENVIRONMENT_NAME');

        try {
            $moduleManager->loadModules();
        } finally {
            if ($environmentName !== false) {
                putenv('ENVIRONMENT_NAME=' . $environmentName);
            }
        }

        return $serviceManager;
    }

    /**
     * Both steps of Application::init(), with the test infrastructure in place before anything is bootstrapped.
     *
     * @param (\Closure(ServiceManager): void)|null $configure runs after the infrastructure is in place and
     *                                                        before bootstrap, with overrides allowed — use it
     *                                                        to replace further services for one test
     */
    public static function bootstrap(Entrypoint $entrypoint, ?\Closure $configure = null): self
    {
        $serviceManager = self::loadModules($entrypoint);
        $logs = new TestHandler();

        $serviceManager->setAllowOverride(true);

        foreach (['default-cache', 'jwks-cache'] as $cache) {
            $serviceManager->setService($cache, new ArrayAdapter());
        }

        $serviceManager->setService('doctrine-cache', self::$doctrineCache ??= new ArrayAdapter());
        $serviceManager->setService('cache.redis.connection', new \Redis());
        $serviceManager->setService(ToggleService::class, new FixedToggleService());

        $translatorPlugins = $serviceManager->get('TranslatorPluginManager');
        assert($translatorPlugins instanceof LoaderPluginManager);
        $translatorPlugins->setAllowOverride(true);
        $translatorPlugins->setService(TranslationLoader::class, new NoTranslations());
        $translatorPlugins->setAllowOverride(false);

        foreach (['Logger', 'ExceptionLogger'] as $name) {
            $logger = $serviceManager->get($name);
            assert($logger instanceof MonologLogger);
            $logger->setHandlers([$logs]);
        }

        if ($configure !== null) {
            $configure($serviceManager);
        }

        $serviceManager->setAllowOverride(false);

        // As Application::init(): listeners named in the merged config, then any in the application config
        $config = $serviceManager->get('config');
        $listeners = array_unique(array_merge(
            $config['listeners'] ?? [],
            $serviceManager->get('ApplicationConfig')['listeners'] ?? []
        ));

        $application = $serviceManager->get('Application');
        assert($application instanceof Application);
        $application->bootstrap($listeners);

        return new self($serviceManager, $application, $logs);
    }

    public static function root(): string
    {
        return DoctrineMetadata::appRoot();
    }

    /**
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    private static function testInfrastructureConfig(array $config): array
    {
        $config['olcs-doctrine']['encryption_key'] = self::ENCRYPTION_KEY;

        foreach (array_keys($config['doctrine']['connection']) as $connection) {
            $params = &$config['doctrine']['connection'][$connection]['params'];
            $params['serverVersion'] = '8.0.0';
            // Anything that does query fails at once, the same way everywhere, instead of resolving a hostname
            $params['host'] = 'localhost';
            $params['unix_socket'] = '/nonexistent/framework-test.sock';
            unset($params);
        }

        return $config;
    }

    /**
     * @param \Closure(array<string, mixed>): array<string, mixed> $change
     */
    private static function changeMergedConfig(ModuleEvent $event, \Closure $change): void
    {
        $configListener = $event->getConfigListener();
        assert($configListener !== null);

        $configListener->setMergedConfig($change($configListener->getMergedConfig(false)));
    }
}
