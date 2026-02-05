<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Service\Queue;

use Dvsa\Olcs\Cli\Service\Queue\MessageConsumerManager;
use Dvsa\Olcs\Cli\Service\Queue\MessageConsumerManagerFactory;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class MessageConsumerManagerFactoryTest extends MockeryTestCase
{
    private MessageConsumerManagerFactory $sut;

    public function setUp(): void
    {
        $this->sut = new MessageConsumerManagerFactory();
    }

    public function testInvoke(): void
    {
        // Params
        $config = [
            'message_consumer_manager' => [
                'invokables' => [
                    'foo' => '\stdClass'
                ]
            ]
        ];

        // Mocks
        $container = m::mock(ContainerInterface::class);
        $container->expects('get')->with('config')->andReturn($config);

        $mcm = $this->sut->__invoke($container, MessageConsumerManager::class);

        $this->assertInstanceOf(MessageConsumerManager::class, $mcm);
        $this->assertTrue($mcm->has('foo'));
        $this->assertFalse($mcm->has('bar'));
    }
}
