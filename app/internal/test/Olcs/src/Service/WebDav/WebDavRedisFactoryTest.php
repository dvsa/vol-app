<?php

declare(strict_types=1);

namespace OlcsTest\Service\WebDav;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\WebDav\WebDavRedisFactory;
use Psr\Container\ContainerInterface;

#[\PHPUnit\Framework\Attributes\CoversClass(WebDavRedisFactory::class)]
final class WebDavRedisFactoryTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function serviceNameConstantIsDefined(): void
    {
        $this->assertSame('WebDavRedis', WebDavRedisFactory::SERVICE_NAME);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeReturnsNullWhenRedisConnectionFails(): void
    {
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                ['Config', [
                    'caches' => [
                        'default-cache' => [
                            'options' => [
                                'server' => [
                                    'host' => 'non-existent-host-that-will-fail',
                                    'port' => 1,
                                ],
                            ],
                        ],
                    ],
                ]],
            ]);

        $factory = new WebDavRedisFactory();
        $result = $factory($container, WebDavRedisFactory::SERVICE_NAME);

        $this->assertNotInstanceOf(\Redis::class, $result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeReturnsNullWhenConnectIsRefused(): void
    {
        // Loopback with a closed port refuses immediately, so this is deterministic and fast
        // (no dependency on a running Redis, no risk of hitting the connect timeout).
        $container = $this->createStub(ContainerInterface::class);
        $container->method('get')
            ->willReturnMap([
                ['Config', [
                    'caches' => [
                        'default-cache' => [
                            'options' => [
                                'server' => [
                                    'host' => '127.0.0.1',
                                    'port' => 1,
                                ],
                            ],
                        ],
                    ],
                ]],
            ]);

        $factory = new WebDavRedisFactory();
        $result = $factory($container, WebDavRedisFactory::SERVICE_NAME);

        $this->assertNotInstanceOf(\Redis::class, $result);
    }
}
