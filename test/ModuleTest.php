<?php

namespace OlcsTest\Logging;

use Laminas\EventManager\EventInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Monolog\Logger as MonologLogger;
use Olcs\Logging\Log\Processor\HidePassword;
use Olcs\Logging\Module;

class ModuleTest extends MockeryTestCase
{
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
}
