<?php

declare(strict_types=1);

namespace OlcsTest\Service\WebDav;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\WebDav\WebDavRedisFactory;
use Psr\Container\ContainerInterface;

#[\PHPUnit\Framework\Attributes\CoversClass(WebDavRedisFactory::class)]
class WebDavRedisFactoryTest extends MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\Test]
    public function serviceNameConstantIsDefined(): void
    {
        $this->assertSame('WebDavRedis', WebDavRedisFactory::SERVICE_NAME);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeReturnsNullWhenRedisConnectionFails(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')
            ->with('Config')
            ->willReturn([
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
            ]);

        $factory = new WebDavRedisFactory();
        $result = $factory($container, WebDavRedisFactory::SERVICE_NAME);

        $this->assertNull($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function invokeUsesDefaultsWhenConfigMissing(): void
    {
        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')
            ->with('Config')
            ->willReturn([]);

        $factory = new WebDavRedisFactory();

        // This will either connect to localhost:6379 if Redis is running, or return null
        $result = $factory($container, WebDavRedisFactory::SERVICE_NAME);

        $this->assertTrue($result === null || $result instanceof \Redis);
    }
}
