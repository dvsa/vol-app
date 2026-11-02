<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Support;

use Doctrine\Deprecations\Deprecation;
use PHPUnit\Event\Application\Finished;
use PHPUnit\Event\Application\FinishedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * Reports the Doctrine deprecations the suite triggered.
 *
 * Doctrine's own reporting cannot be used. Setting DOCTRINE_DEPRECATIONS=trigger makes the
 * library call `@trigger_error()` — with the suppression operator — so PHPUnit's error handler
 * discards it and nothing is displayed. Tracking mode records the same deprecations without
 * raising an error, which is what this reads.
 *
 * Registered by both suites. The unit suite is where CI sees this, and it has something to
 * report because the repository tests build real queries against real entity metadata; the
 * integration suite adds whatever a real connection and schema comparison reach.
 *
 * Reported, never fatal. Tracking mode raises no error at all, so failOnDeprecation is
 * unaffected: these are advance notice of the next major, not a broken build.
 *
 * Under paratest every worker loads this too, but paratest discards a worker's output and
 * each worker sees only the files it ran. So the first process to load it (phpunit, or
 * paratest's parent) creates a hand-off directory, workers inherit its path and leave their
 * counts there, and the report merges them.
 */
final class DoctrineDeprecations implements Extension
{
    private const string HANDOFF_DIR = 'VOL_DOCTRINE_DEPRECATIONS_DIR';

    private static bool $reported = false;

    #[\Override]
    public function bootstrap(
        Configuration $configuration,
        Facade $facade,
        ParameterCollection $parameters,
    ): void {
        Deprecation::enableTrackingDeprecations();

        $handoffDir = getenv(self::HANDOFF_DIR);

        if (is_string($handoffDir) && $handoffDir !== '') {
            self::onFinished($facade, static fn () => file_put_contents(
                $handoffDir . DIRECTORY_SEPARATOR . getmypid(),
                serialize(Deprecation::getTriggeredDeprecations()),
            ));

            return;
        }

        $handoffDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . uniqid('vol-doctrine-deprecations-');
        mkdir($handoffDir);
        // Paratest starts its workers with $_ENV plus the environment phpunit inherited;
        // putenv() alone does not reach them.
        $_ENV[self::HANDOFF_DIR] = $handoffDir;
        putenv(self::HANDOFF_DIR . '=' . $handoffDir);

        // phpunit ran the tests in this process, so its own counts are the run's.
        self::onFinished($facade, static fn () => self::report(Deprecation::getTriggeredDeprecations(), $handoffDir));

        // Paratest's parent never emits Application\Finished. It only builds the suite, which
        // each worker repeats for the files it runs, so the workers' counts alone match what a
        // serial run reports.
        register_shutdown_function(static fn () => self::report([], $handoffDir));
    }

    private static function onFinished(Facade $facade, \Closure $callback): void
    {
        $facade->registerSubscriber(new class ($callback) implements FinishedSubscriber {
            public function __construct(private readonly \Closure $callback)
            {
            }

            #[\Override]
            public function notify(Finished $event): void
            {
                ($this->callback)();
            }
        });
    }

    /**
     * @param array<string, int> $triggered
     */
    private static function report(array $triggered, string $handoffDir): void
    {
        if (self::$reported) {
            return;
        }

        self::$reported = true;

        foreach (glob($handoffDir . DIRECTORY_SEPARATOR . '*') ?: [] as $file) {
            $worker = unserialize((string) file_get_contents($file), ['allowed_classes' => false]);

            foreach (is_array($worker) ? $worker : [] as $link => $count) {
                $triggered[$link] = ($triggered[$link] ?? 0) + $count;
            }

            unlink($file);
        }

        rmdir($handoffDir);

        if ($triggered === []) {
            return;
        }

        // Keyed by the link Doctrine documents each deprecation under; the value is
        // how many times it was reached, which is why a schema comparison shows
        // thousands of one and a single figure of another.
        print PHP_EOL . sprintf(
            '%d distinct Doctrine deprecation%s (%d occurrence%s):',
            count($triggered),
            count($triggered) === 1 ? '' : 's',
            array_sum($triggered),
            array_sum($triggered) === 1 ? '' : 's',
        ) . PHP_EOL;

        arsort($triggered);

        foreach ($triggered as $link => $count) {
            printf('  %dx %s%s', $count, $link, PHP_EOL);
        }

        print PHP_EOL;
    }
}
