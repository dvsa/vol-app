<?php

namespace OlcsTest\Logging\Log;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger as MonologLogger;
use Olcs\Logging\Log\Formatter\Standard;
use Olcs\Logging\Log\LoggerFactory;
use Olcs\Logging\Log\Processor\Microtime;
use Psr\Container\ContainerInterface;
use Psr\Log\LogLevel;

class LoggerFactoryTest extends TestCase
{
    public function testInvokeBuildsMonologLoggerFromConfig(): void
    {
        $container = m::mock(ContainerInterface::class);
        $standard = new Standard();
        $microtime = new Microtime();

        $config = [
            'log' => [
                'Logger' => [
                    'processors' => [
                        ['name' => Microtime::class],
                    ],
                    'writers' => [
                        'full' => [
                            'name' => 'stream',
                            'options' => [
                                'stream' => 'php://memory',
                                'formatter' => Standard::class,
                                'filters' => [
                                    'priority' => ['options' => ['priority' => 4]],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $container->shouldReceive('get')->with('Config')->andReturn($config);
        $container->shouldReceive('has')->with(Standard::class)->andReturn(true);
        $container->shouldReceive('get')->with(Standard::class)->andReturn($standard);
        $container->shouldReceive('has')->with(Microtime::class)->andReturn(true);
        $container->shouldReceive('get')->with(Microtime::class)->andReturn($microtime);

        $sut = new LoggerFactory();
        $logger = $sut($container, 'Logger');

        $this->assertInstanceOf(MonologLogger::class, $logger);
        $this->assertSame('Logger', $logger->getName());

        $handlers = $logger->getHandlers();
        $this->assertCount(1, $handlers);

        $handler = $handlers[0];
        $this->assertInstanceOf(StreamHandler::class, $handler);
        $this->assertSame(Level::Warning, $handler->getLevel());
        $this->assertSame($standard, $handler->getFormatter());

        $this->assertSame([$microtime], $logger->getProcessors());
    }

    public function testInvokeWithMissingServiceUsesDirectInstantiation(): void
    {
        $container = m::mock(ContainerInterface::class);

        $container->shouldReceive('get')->with('Config')->andReturn([
            'log' => [
                'ExceptionLogger' => [
                    'writers' => [
                        'full' => [
                            'name' => 'stream',
                            'options' => [
                                'stream' => 'php://memory',
                                'filters' => [
                                    'priority' => ['options' => ['priority' => 7]],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $sut = new LoggerFactory();
        $logger = $sut($container, 'ExceptionLogger');

        $this->assertSame('ExceptionLogger', $logger->getName());

        $handler = $logger->getHandlers()[0];
        $this->assertInstanceOf(StreamHandler::class, $handler);
        $this->assertSame(Level::Debug, $handler->getLevel());
    }

    /**
     * @dataProvider dpPriorityFormats
     */
    public function testResolveLevelAcceptsBothStringAndIntPriorities(mixed $priority, Level $expected): void
    {
        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with('Config')->andReturn([
            'log' => [
                'Logger' => [
                    'writers' => [
                        'full' => [
                            'name' => 'stream',
                            'options' => [
                                'stream' => 'php://memory',
                                'filters' => ['priority' => ['options' => ['priority' => $priority]]],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $logger = (new LoggerFactory())($container, 'Logger');
        $handler = $logger->getHandlers()[0];

        $this->assertInstanceOf(StreamHandler::class, $handler);
        $this->assertSame($expected, $handler->getLevel());
    }

    public static function dpPriorityFormats(): array
    {
        return [
            'PSR-3 string: debug' => [LogLevel::DEBUG, Level::Debug],
            'PSR-3 string: info' => [LogLevel::INFO, Level::Info],
            'PSR-3 string: warning' => [LogLevel::WARNING, Level::Warning],
            'PSR-3 string: error' => [LogLevel::ERROR, Level::Error],
            'PSR-3 string: critical' => [LogLevel::CRITICAL, Level::Critical],
            'PSR-3 string: emergency' => [LogLevel::EMERGENCY, Level::Emergency],
            'PSR-3 string uppercase' => ['DEBUG', Level::Debug],
            'syslog int 0 (emerg)' => [0, Level::Emergency],
            'syslog int 4 (warn)' => [4, Level::Warning],
            'syslog int 7 (debug)' => [7, Level::Debug],
            'numeric string 4' => ['4', Level::Warning],
        ];
    }

    /**
     * @dataProvider dpInvalidPriorities
     */
    public function testResolveLevelThrowsOnUnrecognisedPriority(mixed $priority): void
    {
        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with('Config')->andReturn([
            'log' => [
                'Logger' => [
                    'writers' => [
                        'full' => [
                            'name' => 'stream',
                            'options' => [
                                'stream' => 'php://memory',
                                'filters' => ['priority' => ['options' => ['priority' => $priority]]],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/^Unrecognised log priority/');

        (new LoggerFactory())($container, 'Logger');
    }

    public static function dpInvalidPriorities(): array
    {
        return [
            'unrecognised string' => ['nonsense'],
            'empty string' => [''],
            'int out of range (high)' => [99],
            'int out of range (negative)' => [-1],
            'numeric string out of range' => ['99'],
            'array' => [['debug']],
            'bool' => [true],
        ];
    }

    public function testResolveLevelThrowsWhenPriorityFilterMissing(): void
    {
        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with('Config')->andReturn([
            'log' => [
                'Logger' => [
                    'writers' => [
                        'full' => [
                            'name' => 'stream',
                            'options' => [
                                'stream' => 'php://memory',
                                // no 'filters' at all — missing config should be loud
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches('/^Unrecognised log priority/');

        (new LoggerFactory())($container, 'Logger');
    }
}
