<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Framework;

use Dvsa\Olcs\Api\Service\Translator\TranslationLoader;
use Dvsa\Olcs\Api\Service\Translator\TranslationLoaderFactory;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Dvsa\OlcsTest\Api\Framework\Support\ApiApplication;
use Dvsa\OlcsTest\Api\Framework\Support\Entrypoint;
use Dvsa\OlcsTest\Api\Framework\Support\FrameworkTestCase;
use Dvsa\OlcsTest\Api\Framework\Support\InertSessionManager;
use Dvsa\OlcsTest\Api\Framework\Support\JsonSnapshot;
use Dvsa\OlcsTest\Api\Framework\Support\PortableValues;
use Dvsa\OlcsTest\Api\Framework\Support\SystemUser;
use Laminas\I18n\Translator\LoaderPluginManager;
use Laminas\ModuleManager\Listener\ServiceListener;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\ServiceManager;
use Olcs\Logging\Log\Processor\SessionId;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;

/**
 * What the api's service container holds, and whether each service in it can still be created.
 *
 * Two snapshots per entrypoint (web: config/container.php, CLI and AWS Batch: config/container-cli.php):
 *
 *  - wiring: every name the bootstrapped container answers to, and how — factory, alias target, delegators,
 *    initializers, sharing — plus which config key feeds each plugin manager. Much of this is not in any api
 *    config file: laminas-mvc registers it implicitly (Request, Response, Application, ViewManager,
 *    ViewHelperManager, ViewResolver, ControllerManager, ControllerPluginManager, the MvcEvent listeners), and
 *    the ModuleManager's ServiceListener decides which config key builds which plugin manager. Removing
 *    laminas-mvc removes all of that at once. When the replacement bootstrap is in place this snapshot shows
 *    exactly which names went missing, and so which ones api code must now register itself: the ~10
 *    factories that ask for 'ViewRenderer' to render snapshots, permit summaries and submission sections,
 *    JWTIdentityProviderFactory's 'Request', and so on.
 *
 *  - creation: the outcome of asking for every name — the class created, or why it could not be — and the
 *    same for every plugin of every plugin manager the api configures (command, query and validation
 *    handlers, repositories, controllers, view helpers, ...). A service that today fails only because the
 *    unit suite has no AWS or remote endpoints is pinned as failing, with its reason; a service that stops
 *    being creatable because something it depends on vanished shows up as a new failure.
 *
 * The container's internals are read by reflection. That is acceptable here because laminas-servicemanager
 * v3 stays through the laminas-mvc removal (only the MVC glue is replaced; SM v4 is a separate epic); if the
 * container class itself changes, rewrite the reader, not the snapshots.
 *
 * The creation test runs in its own process, the one exception among the Framework tests. Creating every
 * service loads every class it reaches, and other tests in the suite define classes too: Mockery `overload:`
 * mocks replace real classes (\Aws\Sts\StsClient) for the rest of a process, and mocks of non-existent
 * names declare them (`IrhpApplication`). In a shared process the outcome would depend on which tests ran
 * first, and the real classes it loads would break later overload mocks.
 *
 * Regenerate with UPDATE_SNAPSHOTS=1 only for an intended change, and review the diff.
 */
final class ContainerTest extends FrameworkTestCase
{
    private const string SNAPSHOT_DIR = __DIR__ . '/snapshots/container';
    private const string UPDATE_COMMAND = 'UPDATE_SNAPSHOTS=1 vendor/bin/phpunit --filter ContainerTest';

    /**
     * Error levels recorded against the service being created. Deprecations are left to PHPUnit, as in any
     * other test, so a PHP upgrade's deprecations do not churn the snapshot.
     */
    private const int RECORDED_ERRORS = E_WARNING | E_NOTICE | E_USER_WARNING | E_USER_NOTICE;

    #[DataProvider('entrypoints')]
    public function testWiringMatchesSnapshot(Entrypoint $entrypoint): void
    {
        $serviceManager = $this->boot($entrypoint)->serviceManager;

        JsonSnapshot::assertMatches(
            self::snapshotFile($entrypoint, 'wiring'),
            $this->wiring($serviceManager),
            sprintf('The %s container wiring', $entrypoint->value),
            self::UPDATE_COMMAND
        );
    }

    #[DataProvider('entrypoints')]
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testEveryServiceIsCreatedAsBefore(Entrypoint $entrypoint): void
    {
        $sessionStatus = session_status();
        $serviceManager = $this->boot($entrypoint, withRealTranslationLoader: true)->serviceManager;
        $services = [];

        foreach (self::names($serviceManager) as $name) {
            $services[$name] = $this->create($serviceManager, $name);
        }

        JsonSnapshot::assertMatches(
            self::snapshotFile($entrypoint, 'creation'),
            ['services' => $services, 'plugin_managers' => $this->pluginManagerOutcomes($serviceManager)],
            sprintf('Creating every %s service', $entrypoint->value),
            self::UPDATE_COMMAND
        );

        $this->assertSame($sessionStatus, session_status(), 'Creating the services started a PHP session');
    }

    public function testEverySnapshotBelongsToATest(): void
    {
        $expected = [];

        foreach (Entrypoint::cases() as $entrypoint) {
            $expected[] = self::snapshotFile($entrypoint, 'wiring');
            $expected[] = self::snapshotFile($entrypoint, 'creation');
        }

        JsonSnapshot::assertNoOrphans(self::SNAPSHOT_DIR, $expected, self::UPDATE_COMMAND);
    }

    /**
     * @return iterable<string, array{Entrypoint}>
     */
    public static function entrypoints(): iterable
    {
        foreach (Entrypoint::cases() as $entrypoint) {
            yield $entrypoint->value => [$entrypoint];
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function wiring(ServiceManager $serviceManager): array
    {
        $factories = self::internal($serviceManager, 'factories');
        $aliases = self::internal($serviceManager, 'aliases');

        ksort($factories, SORT_STRING);
        ksort($aliases, SORT_STRING);

        // Created services are cached alongside the ones set directly; only the latter are wiring
        $services = array_diff_key(self::internal($serviceManager, 'services'), $factories, $aliases);
        ksort($services, SORT_STRING);

        $delegators = self::internal($serviceManager, 'delegators');
        ksort($delegators, SORT_STRING);

        $shared = self::internal($serviceManager, 'shared');
        ksort($shared, SORT_STRING);

        $lazyServices = self::internal($serviceManager, 'lazyServices');
        ksort($lazyServices, SORT_STRING);

        return [
            'factories' => array_map(self::describe(...), $factories),
            'aliases' => $aliases,
            'services' => array_map(self::describe(...), $services),
            'abstract_factories' => array_map(self::describe(...), array_values(self::internal($serviceManager, 'abstractFactories'))),
            'delegators' => array_map(static fn(array $chain): array => array_map(self::describe(...), $chain), $delegators),
            'initializers' => array_map(self::describe(...), self::internal($serviceManager, 'initializers')),
            'shared' => $shared,
            'shared_by_default' => (new \ReflectionProperty(ServiceManager::class, 'sharedByDefault'))->getValue($serviceManager),
            'lazy_services' => PortableValues::of($lazyServices),
            'plugin_manager_config_keys' => self::pluginManagerConfigKeys($serviceManager),
        ];
    }

    /**
     * The outcome of creating every plugin of every plugin manager the api configures.
     *
     * Plugin managers in the api's own namespaces (command/query/validation handlers, repositories,
     * publication and submission plugins, ...) are checked in full. Laminas' own managers (controllers,
     * controller plugins, view helpers, validators, filters, ...) ship hundreds of built-ins, which would tie
     * the snapshot to laminas releases, so only the plugins the api's config registers in them are checked.
     *
     * Only failures are listed, with a count of the plugins checked: the command-to-handler maps themselves
     * are in the merged config snapshot (MergedConfigTest).
     *
     * @return array<string, array<string, mixed>>
     */
    private function pluginManagerOutcomes(ServiceManager $serviceManager): array
    {
        $config = $serviceManager->get('config');
        $managers = [];
        $outcomes = [];
        $seen = [];

        // Laminas' own managers under the names the ServiceListener gives them; most are also reachable by
        // their class name, which would otherwise come first in the sort and hide the config key
        foreach (self::pluginManagerConfigKeys($serviceManager) as $name => $configKey) {
            $manager = $this->create($serviceManager, $name);

            if (is_array($manager)) {
                $outcomes[$name] = ['config_key' => $configKey] + $manager;
                continue;
            }

            $manager = $serviceManager->get($name);
            assert($manager instanceof AbstractPluginManager);
            $seen[spl_object_id($manager)] = true;
            $managers[$name] = [$manager, self::configuredNames($config[$configKey] ?? [])];
        }

        foreach (self::names($serviceManager) as $name) {
            try {
                $manager = $serviceManager->get($name);
            } catch (\Throwable) {
                continue;
            }

            if (
                $manager instanceof AbstractPluginManager
                && self::isOwnedByTheApi($manager)
                && !isset($seen[spl_object_id($manager)])
            ) {
                $seen[spl_object_id($manager)] = true;
                $managers[$name] = [$manager, self::names($manager)];
            }
        }

        $configKeys = self::pluginManagerConfigKeys($serviceManager);

        foreach ($managers as $name => [$manager, $plugins]) {
            $notCreated = [];

            foreach ($plugins as $plugin) {
                $outcome = $this->create($manager, $plugin);

                if (!is_string($outcome)) {
                    $notCreated[$plugin] = $outcome;
                }
            }

            $outcomes[$name] = [
                'class' => $manager::class,
                'config_key' => $configKeys[$name] ?? null,
                'plugins' => count($plugins),
                'not_created' => $notCreated,
            ];
        }

        ksort($outcomes, SORT_STRING);

        return $outcomes;
    }

    /**
     * The created class, or why the service could not be created. Warnings and notices raised while creating
     * it (typically a factory reading a config key only local.php provides) are recorded, not raised.
     *
     * @return string|array{created?: string, error?: string, warnings?: list<string>}
     */
    private function create(ServiceManager $container, string $name): string|array
    {
        $warnings = [];
        $previous = null;
        $previous = set_error_handler(
            static function (int $level, string $message, string $file = '', int $line = 0) use (&$warnings, &$previous): bool {
                if (($level & self::RECORDED_ERRORS) === 0) {
                    return is_callable($previous) && (bool) $previous($level, $message, $file, $line);
                }

                $warnings[] = PortableValues::message($message) . ' in ' . PortableValues::string($file);

                return true;
            }
        );

        try {
            $service = $container->get($name);
            $outcome = ['created' => self::describeCreated($service)];
        } catch (\Throwable $e) {
            $outcome = ['error' => $e::class . ': ' . PortableValues::message($e->getMessage())];
        } finally {
            restore_error_handler();
        }

        if ($warnings === []) {
            return $outcome['created'] ?? $outcome;
        }

        $outcome['warnings'] = array_values(array_unique($warnings));

        return $outcome;
    }

    /**
     * Boots the real application, with logging kept from starting a PHP session.
     *
     * The logger's SessionId processor calls SessionManager::start() for every record, and creating the
     * services writes records (factories that catch a failure log it). Inside PHPUnit no headers have been
     * sent, so that would start a real PHP session that outlives the test; the processor is given
     * InertSessionManager instead. (ApiApplication points the database at a socket that does not exist, so
     * anything that queries it while being created fails at once, the same way on every machine.)
     */
    private function boot(Entrypoint $entrypoint, bool $withRealTranslationLoader = false): ApiApplication
    {
        return ApiApplication::bootstrap(
            $entrypoint,
            static function (ServiceManager $serviceManager) use ($entrypoint, $withRealTranslationLoader): void {
                if ($withRealTranslationLoader) {
                    self::useRealTranslationLoader($serviceManager);
                }

                $sessionId = $serviceManager->get(SessionId::class);
                assert($sessionId instanceof SessionId);
                $sessionId->setSessionManager(new InertSessionManager());

                if ($entrypoint === Entrypoint::Cli) {
                    SystemUser::answerTheBootstrapLookup($serviceManager);
                }
            }
        );
    }

    /**
     * Creating the translator is part of what the creation snapshot checks (most validators and the translate
     * view helpers need it), so the real remote loader is put back in place of ApiApplication's stand-in.
     *
     * The cache is warmed as in a running environment: on a cold one, creating the translator reads the
     * replacement table (TranslationLoader::loadReplacements), and the unit suite has no database.
     */
    private static function useRealTranslationLoader(ServiceManager $serviceManager): void
    {
        $cache = $serviceManager->get(CacheEncryption::class);
        assert($cache instanceof CacheEncryption);
        $cache->setCustomItem(
            CacheEncryption::TRANSLATION_REPLACEMENT_IDENTIFIER,
            ['{framework-test}' => 'framework test replacement']
        );

        $translatorPlugins = $serviceManager->get('TranslatorPluginManager');
        assert($translatorPlugins instanceof LoaderPluginManager);
        $translatorPlugins->setAllowOverride(true);
        $translatorPlugins->setService(
            TranslationLoader::class,
            (new TranslationLoaderFactory())($serviceManager, TranslationLoader::class)
        );
        $translatorPlugins->setAllowOverride(false);
    }

    /**
     * Which config key builds which plugin manager. The ModuleManager's ServiceListener holds this map; it is
     * laminas-modulemanager machinery, so the replacement bootstrap has to reproduce it.
     *
     * @return array<string, string>
     */
    private static function pluginManagerConfigKeys(ServiceManager $serviceManager): array
    {
        $listener = $serviceManager->get('ServiceListener');
        assert($listener instanceof ServiceListener);

        $keys = [];

        foreach ((new \ReflectionProperty(ServiceListener::class, 'serviceManagers'))->getValue($listener) as $manager) {
            // The application container itself is keyed by object hash; it is the 'service_manager' key
            if (is_string($manager['service_manager'])) {
                $keys[$manager['service_manager']] = $manager['config_key'];
            }
        }

        ksort($keys, SORT_STRING);

        return $keys;
    }

    /**
     * @return list<string>
     */
    private static function names(ServiceManager $container): array
    {
        $names = array_keys(
            self::internal($container, 'factories')
            + self::internal($container, 'aliases')
            + self::internal($container, 'services')
        );
        $names = array_map('strval', $names);
        sort($names, SORT_STRING);

        return $names;
    }

    /**
     * @param array<string, mixed> $config a plugin manager's section of the merged config
     *
     * @return list<string>
     */
    private static function configuredNames(array $config): array
    {
        $names = [];

        foreach (['factories', 'invokables', 'aliases', 'services'] as $type) {
            foreach (array_keys($config[$type] ?? []) as $name) {
                $names[] = (string) $name;
            }
        }

        $names = array_values(array_unique($names));
        sort($names, SORT_STRING);

        return $names;
    }

    private static function isOwnedByTheApi(object $manager): bool
    {
        return str_starts_with($manager::class, 'Dvsa\\') || str_starts_with($manager::class, 'Olcs\\');
    }

    /**
     * @return array<array-key, mixed>
     */
    private static function internal(ServiceManager $container, string $property): array
    {
        $value = (new \ReflectionProperty(ServiceManager::class, $property))->getValue($container);
        assert(is_array($value));

        return $value;
    }

    private static function describe(mixed $value): mixed
    {
        return match (true) {
            is_string($value) => $value,
            is_array($value) => '(array)',
            default => PortableValues::of($value),
        };
    }

    private static function describeCreated(mixed $service): string
    {
        if (!is_object($service)) {
            return '(' . get_debug_type($service) . ')';
        }

        // get_debug_type() names anonymous classes without their file path and line
        return (string) preg_replace('/^Mockery_\d+_/', 'Mockery_', get_debug_type($service));
    }

    private static function snapshotFile(Entrypoint $entrypoint, string $name): string
    {
        return self::SNAPSHOT_DIR . '/' . $entrypoint->value . '/' . $name . '.json';
    }
}
