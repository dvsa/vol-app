<?php

declare(strict_types=1);

namespace OlcsTest\Logging;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Monolog\Logger as MonologLogger;
use Olcs\Logging\Log\Logger as StaticLogger;
use Olcs\Logging\Log\Processor\HideCredentials;
use Olcs\Logging\Log\Processor\HidePassword;
use Olcs\Logging\Module;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

final class ModuleTest extends MockeryTestCase
{
    protected function tearDown(): void
    {
        // Reset the static logger so a closed mock can't leak into later tests.
        StaticLogger::setLogger(new NullLogger());
        parent::tearDown();
    }

    public function testGetConfig(): void
    {
        $sut = new Module();
        $config = $sut->getConfig();

        $this->assertArrayHasKey('log', $config);
        $this->assertArrayHasKey('service_manager', $config);
        $this->assertArrayHasKey('Logger', $config['service_manager']['factories']);
        $this->assertArrayHasKey('ExceptionLogger', $config['service_manager']['factories']);

        // The MvcEvent listeners live in olcs/vol-logging-mvc now. Core contributing a
        // 'listeners' key again would drag laminas-mvc back into this package.
        $this->assertArrayNotHasKey('listeners', $config);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestBootstrapLogger')]
    public function testBootstrapLogger(int $hideTimes, array $logConfig): void
    {
        $logger = m::mock(MonologLogger::class);
        $hidePassword = m::mock(HidePassword::class);
        $hideCredentials = m::mock(HideCredentials::class);

        // Typed, because bootstrapLogger only ever calls get() on it. An untyped m::mock() also
        // works at runtime but leaves phpstan-mockery unable to narrow expects(), which then
        // reports with() as undefined on the union it falls back to.
        $container = m::mock(ContainerInterface::class);
        $container->expects('get')->with('Logger')->andReturn($logger);
        // times() rather than expects(), because $hideTimes is 0 in the allow-logging cases.
        $container->shouldReceive('get')->with(HidePassword::class)->times($hideTimes)->andReturn($hidePassword);
        // Always attached, whatever allowPasswordLogging says.
        $container->expects('get')->with(HideCredentials::class)->andReturn($hideCredentials);

        // Split by processor rather than counted in aggregate, so the test says which one was
        // attached. HideCredentials is unconditional; HidePassword only when password logging is
        // disallowed, which is why it keeps times() instead of expects().
        $logger->expects('pushProcessor')->with($hideCredentials)->andReturnSelf();
        $logger->shouldReceive('pushProcessor')->with($hidePassword)->times($hideTimes)->andReturnSelf();

        new Module()->bootstrapLogger($container, $logConfig);

        // bootstrapLogger permanently installs Monolog's error + exception handlers
        // (via ErrorHandler::register) and the user-error tolerance handler as a
        // production side effect. Unwind them so the test leaves global handler
        // state untouched (PHPUnit failOnRisky).
        restore_error_handler();      // user-error tolerance handler
        restore_error_handler();      // Monolog error handler
        restore_exception_handler();  // Monolog exception handler
    }

    public static function dpTestBootstrapLogger(): \Iterator
    {
        yield 'noConfigEntry' => [1, []];
        yield 'allowTrue' => [0, ['log' => ['allowPasswordLogging' => true]]];
        yield 'allowFalse' => [1, ['log' => ['allowPasswordLogging' => false]]];
        yield 'allowAmbiguous' => [0, ['log' => ['allowPasswordLogging' => 'somestring']]];
    }

    public function testToleranceHandlerToleratesUserError(): void
    {
        $logger = m::mock(LoggerInterface::class);
        $logger->expects('error')
            ->with(
                'TOLERATED_USER_ERROR: boom',
                m::on(static fn (array $ctx): bool =>
                    ($ctx['tag'] ?? null) === 'tolerated-user-error'
                    && ($ctx['errno'] ?? null) === E_USER_ERROR
                    && ($ctx['file'] ?? null) === '/x.php'
                    && ($ctx['line'] ?? null) === 42)
            );
        StaticLogger::setLogger($logger);

        $previousCalled = false;
        $previous = static function () use (&$previousCalled): bool {
            $previousCalled = true;
            return false;
        };

        $handler = new Module()->makeToleranceHandler($previous);
        $result = $handler(E_USER_ERROR, 'boom', '/x.php', 42);

        $this->assertTrue($result, 'E_USER_ERROR must be tolerated (return true) so execution continues');
        $this->assertFalse($previousCalled, 'E_USER_ERROR is handled locally, not delegated to the previous handler');
    }

    public function testToleranceHandlerDelegatesOtherLevelsToPreviousHandler(): void
    {
        $logger = m::mock(LoggerInterface::class);
        $logger->shouldReceive('error')->withAnyArgs()->never();
        StaticLogger::setLogger($logger);

        $previousArgs = null;
        $previous = static function (int $errno, string $message, string $file = '', int $line = 0) use (&$previousArgs): bool {
            $previousArgs = [$errno, $message, $file, $line];
            return true;
        };

        $handler = new Module()->makeToleranceHandler($previous);
        $result = $handler(E_USER_WARNING, 'warn', '/y.php', 7);

        $this->assertTrue($result, 'the previous handler return value should propagate');
        $this->assertSame([E_USER_WARNING, 'warn', '/y.php', 7], $previousArgs);
    }

    public function testToleranceHandlerReturnsFalseForOtherLevelsWhenNoPreviousHandler(): void
    {
        $handler = new Module()->makeToleranceHandler(null);

        $this->assertFalse(
            $handler(E_USER_WARNING, 'warn', '/z.php', 1),
            'with no previous handler, non-E_USER_ERROR levels fall through to PHP (return false)'
        );
    }
}
