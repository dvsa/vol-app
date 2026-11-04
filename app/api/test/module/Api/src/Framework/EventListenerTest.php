<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Framework;

use Dvsa\OlcsTest\Api\Framework\Support\ApiApplication;
use Dvsa\OlcsTest\Api\Framework\Support\Entrypoint;
use Dvsa\OlcsTest\Api\Framework\Support\FrameworkTestCase;
use Dvsa\OlcsTest\Api\Framework\Support\JsonSnapshot;
use Dvsa\OlcsTest\Api\Framework\Support\SystemUser;
use Laminas\EventManager\EventManager;
use Laminas\EventManager\EventsCapableInterface;
use Laminas\EventManager\SharedEventManager;
use Laminas\EventManager\SharedEventManagerInterface;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Every listener the booted api has attached, in the order each event runs them.
 *
 * Under laminas-mvc a request is a sequence of events — route, dispatch, render, finish — and almost
 * everything the api does around its controllers is a listener on one of them: laminas-mvc's own routing,
 * dispatch, error and view strategies and response sending, and the api's PayloadValidationListener, the Db
 * module's ModuleRouteListener and the response logging. Most are attached by an onBootstrap or by the
 * ViewManager, not declared in any config file. A PSR-15 pipeline has no events, so each entry here has to
 * become middleware (or a decorator, or nothing) in the right position. This is that list. HttpContractTest
 * pins what the listeners do to a response; this pins which ones there are and their order.
 *
 * Three event managers are read, each as its own trigger would run it: its own listeners, the shared
 * listeners for its identifiers and the wildcards, highest priority first and, at equal priority, in the
 * order they were attached (EventManager::triggerListeners()). They are the Application's (the MVC events),
 * the SendResponseListener's (send.response) and the View's (renderer, response). The shared listeners on any
 * other identifier — controllers and the dispatchable interfaces, for the controller's own dispatch event —
 * are listed by identifier.
 *
 * Listeners are named Class::method. A closure is named for where it is defined, its line number dropped so
 * that unrelated edits to the file leave the snapshot alone.
 *
 * Regenerate with UPDATE_SNAPSHOTS=1 only for an intended change, and review the diff.
 */
final class EventListenerTest extends FrameworkTestCase
{
    private const string SNAPSHOT_DIR = __DIR__ . '/snapshots/listeners';
    private const string UPDATE_COMMAND = 'UPDATE_SNAPSHOTS=1 vendor/bin/phpunit --filter EventListenerTest';

    /**
     * Event managers read as their trigger runs them, by the service that holds them
     */
    private const array EVENT_MANAGERS = [
        'application' => 'Application',
        'send response' => 'SendResponseListener',
        'view' => 'View',
    ];

    /**
     * @return iterable<string, array{Entrypoint}>
     */
    public static function entrypoints(): iterable
    {
        foreach (Entrypoint::cases() as $entrypoint) {
            yield $entrypoint->value => [$entrypoint];
        }
    }

    #[DataProvider('entrypoints')]
    public function testListenersMatchSnapshot(Entrypoint $entrypoint): void
    {
        $serviceManager = ApiApplication::bootstrap(
            $entrypoint,
            $entrypoint === Entrypoint::Cli ? SystemUser::answerTheBootstrapLookup(...) : null
        )->serviceManager;

        $inventory = [];
        $sharedManager = null;
        $covered = [];

        foreach (self::EVENT_MANAGERS as $label => $service) {
            $holder = $serviceManager->get($service);
            assert($holder instanceof EventsCapableInterface);
            $events = $holder->getEventManager();
            assert($events instanceof EventManager);

            $sharedManager ??= $events->getSharedManager();
            $this->assertSame($sharedManager, $events->getSharedManager(), $label . ' has its own shared manager');

            $identifiers = $events->getIdentifiers();
            $covered = [...$covered, ...$identifiers];

            $inventory[$label] = [
                'identifiers' => $identifiers,
                'events' => self::triggerOrder($events),
            ];
        }

        assert($sharedManager instanceof SharedEventManager);
        $inventory['shared, other identifiers'] = self::sharedListeners($sharedManager, $covered);

        JsonSnapshot::assertMatches(
            self::SNAPSHOT_DIR . '/' . $entrypoint->value . '.json',
            $inventory,
            'The listeners attached when the api boots (' . $entrypoint->value . ')',
            self::UPDATE_COMMAND
        );
    }

    public function testEverySnapshotBelongsToATest(): void
    {
        $expected = [];

        foreach (Entrypoint::cases() as $entrypoint) {
            $expected[] = self::SNAPSHOT_DIR . '/' . $entrypoint->value . '.json';
        }

        JsonSnapshot::assertNoOrphans(self::SNAPSHOT_DIR, $expected, self::UPDATE_COMMAND);
    }

    /**
     * For every event with a listener, the listeners in the order EventManager::triggerListeners() calls them.
     *
     * @return array<string, list<string>>
     */
    private static function triggerOrder(EventManager $events): array
    {
        $own = self::property($events, 'events');
        $shared = self::property($events->getSharedManager(), 'identifiers');
        $identifiers = $events->getIdentifiers();

        $names = array_keys($own);

        foreach ([...$identifiers, '*'] as $identifier) {
            $names = [...$names, ...array_keys($shared[$identifier] ?? [])];
        }

        $names = array_values(array_unique(array_filter($names, static fn(string $name): bool => $name !== '*')));
        sort($names);

        $order = [];

        foreach ($names as $name) {
            // As triggerListeners(): the event's own, then its wildcard ones, then the shared ones, per priority
            $byPriority = [];

            foreach ($own[$name] ?? [] as $priority => $listOfListeners) {
                $byPriority[$priority][] = $listOfListeners[0];
            }

            foreach ($own['*'] ?? [] as $priority => $listOfListeners) {
                $byPriority[$priority][] = $listOfListeners[0];
            }

            $sharedManager = $events->getSharedManager();
            assert($sharedManager instanceof SharedEventManagerInterface);

            foreach ($sharedManager->getListeners($identifiers, $name) as $priority => $listeners) {
                $byPriority[$priority][] = $listeners;
            }

            krsort($byPriority);

            foreach ($byPriority as $priority => $listOfListeners) {
                foreach ($listOfListeners as $listeners) {
                    foreach ($listeners as $listener) {
                        $order[$name][] = self::entry($priority, $listener);
                    }
                }
            }
        }

        return $order;
    }

    /**
     * @param list<string> $covered identifiers already listed with an event manager
     *
     * @return array<string, array<string, list<string>>> identifier => event => listeners
     */
    private static function sharedListeners(SharedEventManager $sharedManager, array $covered): array
    {
        $listed = [];

        foreach (self::property($sharedManager, 'identifiers') as $identifier => $events) {
            if (in_array($identifier, $covered, true)) {
                continue;
            }

            foreach ($events as $name => $byPriority) {
                krsort($byPriority);

                foreach ($byPriority as $priority => $listeners) {
                    foreach ($listeners as $listener) {
                        $listed[$identifier][$name][] = self::entry($priority, $listener);
                    }
                }
            }

            ksort($listed[$identifier]);
        }

        ksort($listed);

        return $listed;
    }

    private static function entry(int $priority, callable $listener): string
    {
        return sprintf('%d %s', $priority, self::describe($listener));
    }

    private static function describe(callable $listener): string
    {
        if (is_array($listener)) {
            [$target, $method] = $listener;

            return self::className(is_object($target) ? $target : (string) $target) . '::' . $method;
        }

        if ($listener instanceof \Closure) {
            $function = new \ReflectionFunction($listener);
            $name = $function->getName();

            // PHP 8.4 names a closure for where it is defined: {closure:Class::method():LINE}
            if (str_starts_with($name, '{closure')) {
                return (string) preg_replace('/:\d+\}$/', '}', $name);
            }

            // A first-class callable, Class::method(...)
            $class = $function->getClosureScopeClass();

            return ($class === null ? '' : self::className($class->getName()) . '::') . $name;
        }

        if (is_object($listener)) {
            return self::className($listener) . '::__invoke';
        }

        if (is_string($listener)) {
            return $listener;
        }

        throw new \LogicException('A listener of type ' . get_debug_type($listener) . ' is not described');
    }

    /**
     * An anonymous class's name holds the path of the file it is in, which differs between machines
     */
    private static function className(object|string $classOrObject): string
    {
        $class = new \ReflectionClass($classOrObject);

        if (!$class->isAnonymous()) {
            return $class->getName();
        }

        $parent = $class->getParentClass();

        return 'class@anonymous'
            . ($parent === false ? '' : ' extends ' . $parent->getName())
            . ($class->getInterfaceNames() === [] ? '' : ' implements ' . implode(', ', $class->getInterfaceNames()));
    }

    /**
     * @return array<mixed>
     */
    private static function property(object $object, string $name): array
    {
        $value = (new \ReflectionProperty($object, $name))->getValue($object);
        assert(is_array($value));

        return $value;
    }
}
