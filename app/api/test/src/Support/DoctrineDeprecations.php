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
 */
final class DoctrineDeprecations implements Extension
{
    #[\Override]
    public function bootstrap(
        Configuration $configuration,
        Facade $facade,
        ParameterCollection $parameters,
    ): void {
        Deprecation::enableTrackingDeprecations();

        $facade->registerSubscriber(new class implements FinishedSubscriber {
            #[\Override]
            public function notify(Finished $event): void
            {
                $triggered = Deprecation::getTriggeredDeprecations();

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
        });
    }
}
