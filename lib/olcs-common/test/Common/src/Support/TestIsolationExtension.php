<?php

declare(strict_types=1);

namespace CommonTest\Support;

use Mockery;
use PHPUnit\Event\Test\Finished;
use PHPUnit\Event\Test\FinishedSubscriber;
use PHPUnit\Runner\Extension\Extension;
use PHPUnit\Runner\Extension\Facade;
use PHPUnit\Runner\Extension\ParameterCollection;
use PHPUnit\TextUI\Configuration\Configuration;

/**
 * Isolates process-global state between tests so that execution order (random by
 * configuration) cannot make tests contaminate one another.
 *
 * Two leaks are addressed:
 *
 *  - Mockery container: tests that use Mockery while extending the plain PHPUnit
 *    TestCase (rather than MockeryTestCase) never call Mockery::close(), so their
 *    mock expectations linger in Mockery's global container and are counted toward
 *    the next MockeryTestCase's assertion tally, which can make an
 *    "expectNotToPerformAssertions" test spuriously risky.
 *
 *  - json_last_error(): some tests deliberately leave the global JSON error state
 *    dirty (e.g. when covering a decode-failure branch). Code under test such as
 *    Common\Service\Cqrs\CqrsTrait inspects json_last_error() and can then take an
 *    unexpected error path in a later, unrelated test.
 */
final class TestIsolationExtension implements Extension
{
    #[\Override]
    public function bootstrap(Configuration $configuration, Facade $facade, ParameterCollection $parameters): void
    {
        $facade->registerSubscriber(new class implements FinishedSubscriber {
            #[\Override]
            public function notify(Finished $event): void
            {
                Mockery::resetContainer();

                // Clear any lingering JSON error via a successful, side-effect-free call.
                json_encode(null);
            }
        });
    }
}
