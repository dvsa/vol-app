<?php

namespace OlcsTest\Logging;

use Laminas\EventManager\EventInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Monolog\Logger as MonologLogger;
use Olcs\Logging\Log\Logger as StaticLogger;
use Olcs\Logging\Log\Processor\HidePassword;
use Olcs\Logging\Module;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ModuleTest extends MockeryTestCase
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
        $this->assertArrayHasKey('listeners', $config);
        $this->assertArrayHasKey('service_manager', $config);
        $this->assertArrayHasKey('Logger', $config['service_manager']['factories']);
        $this->assertArrayHasKey('ExceptionLogger', $config['service_manager']['factories']);
    }

    /**
     * @dataProvider dpTestOnBootstrap
     */
    public function testOnBootstrap(int $hideTimes, array $logConfig): void
    {
        $event = m::mock(EventInterface::class);
        $logger = m::mock(MonologLogger::class);
        $hidePassword = m::mock(HidePassword::class);

        $serviceManager = m::mock();
        $serviceManager->shouldReceive('get')->with('Logger')->once()->andReturn($logger);
        $serviceManager->shouldReceive('get')->with('Config')->once()->andReturn($logConfig);
        $serviceManager->shouldReceive('get')->with(HidePassword::class)->times($hideTimes)->andReturn($hidePassword);

        $event->shouldReceive('getApplication->getServiceManager')->andReturn($serviceManager);

        $logger->shouldReceive('pushProcessor')
            ->times($hideTimes)
            ->andReturnSelf();

        $sut = new Module();
        $sut->onBootstrap($event);
    }

    public static function dpTestOnBootstrap(): array
    {
        return [
            'noConfigEntry' => [1, []],
            'allowTrue' => [0, ['log' => ['allowPasswordLogging' => true]]],
            'allowFalse' => [1, ['log' => ['allowPasswordLogging' => false]]],
            'allowAmbiguous' => [0, ['log' => ['allowPasswordLogging' => 'somestring']]],
        ];
    }

    public function testToleranceHandlerToleratesUserError(): void
    {
        $logger = m::mock(LoggerInterface::class);
        $logger->shouldReceive('error')
            ->once()
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

        $handler = (new Module())->makeToleranceHandler($previous);
        $result = $handler(E_USER_ERROR, 'boom', '/x.php', 42);

        $this->assertTrue($result, 'E_USER_ERROR must be tolerated (return true) so execution continues');
        $this->assertFalse($previousCalled, 'E_USER_ERROR is handled locally, not delegated to the previous handler');
    }

    public function testToleranceHandlerDelegatesOtherLevelsToPreviousHandler(): void
    {
        $logger = m::mock(LoggerInterface::class);
        $logger->shouldReceive('error')->never();
        StaticLogger::setLogger($logger);

        $previousArgs = null;
        $previous = static function (int $errno, string $message, string $file = '', int $line = 0) use (&$previousArgs): bool {
            $previousArgs = [$errno, $message, $file, $line];
            return true;
        };

        $handler = (new Module())->makeToleranceHandler($previous);
        $result = $handler(E_USER_WARNING, 'warn', '/y.php', 7);

        $this->assertTrue($result, 'the previous handler return value should propagate');
        $this->assertSame([E_USER_WARNING, 'warn', '/y.php', 7], $previousArgs);
    }

    public function testToleranceHandlerReturnsFalseForOtherLevelsWhenNoPreviousHandler(): void
    {
        $handler = (new Module())->makeToleranceHandler(null);

        $this->assertFalse(
            $handler(E_USER_WARNING, 'warn', '/z.php', 1),
            'with no previous handler, non-E_USER_ERROR levels fall through to PHP (return false)'
        );
    }
}
