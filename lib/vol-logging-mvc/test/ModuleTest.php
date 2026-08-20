<?php

declare(strict_types=1);

namespace OlcsTest\Logging\Mvc;

use Laminas\Mvc\MvcEvent;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Monolog\Logger as MonologLogger;
use Olcs\Logging\Log\Logger as StaticLogger;
use Olcs\Logging\Log\Processor\HideCredentials;
use Olcs\Logging\Mvc\Listener\LogError;
use Olcs\Logging\Mvc\Listener\LogRequest;
use Olcs\Logging\Mvc\Module;
use Psr\Container\ContainerInterface;
use Psr\Log\NullLogger;

final class ModuleTest extends MockeryTestCase
{
    protected function tearDown(): void
    {
        // Reset the static logger so a closed mock can't leak into later tests.
        StaticLogger::setLogger(new NullLogger());
        parent::tearDown();
    }

    public function testGetConfigRegistersTheMvcListeners(): void
    {
        $config = new Module()->getConfig();

        $this->assertSame([LogRequest::class, LogError::class], $config['listeners']);

        // The listeners are this package's to register: core no longer ships them, so
        // nothing else would put their factories in the container.
        $factories = $config['service_manager']['factories'];
        $this->assertArrayHasKey(LogRequest::class, $factories);
        $this->assertArrayHasKey(LogError::class, $factories);
    }

    public function testOnBootstrapDelegatesToCoreBootstrapLogger(): void
    {
        $logger = m::mock(MonologLogger::class);
        $hideCredentials = m::mock(HideCredentials::class);

        $container = m::mock(ContainerInterface::class);
        $container->expects('get')->with('Logger')->andReturn($logger);
        // allowPasswordLogging true keeps HidePassword out of it; the conditional itself is
        // core's to cover, this test only proves the delegation happens.
        $container->expects('get')->with('Config')->andReturn(['log' => ['allowPasswordLogging' => true]]);
        $container->expects('get')->with(HideCredentials::class)->andReturn($hideCredentials);

        $logger->expects('pushProcessor')->with($hideCredentials)->andReturnSelf();

        $event = m::mock(MvcEvent::class);
        $event->shouldReceive('getApplication->getServiceManager')->withNoArgs()->andReturn($container);

        new Module()->onBootstrap($event);

        $this->assertSame(
            $logger,
            StaticLogger::getLogger(),
            'core bootstrapLogger() should have run and installed the logger on the static facade'
        );

        // bootstrapLogger installs Monolog's error + exception handlers and the user-error
        // tolerance handler as a production side effect. Unwind them so the test leaves
        // global handler state untouched (PHPUnit failOnRisky).
        restore_error_handler();      // user-error tolerance handler
        restore_error_handler();      // Monolog error handler
        restore_exception_handler();  // Monolog exception handler
    }
}
