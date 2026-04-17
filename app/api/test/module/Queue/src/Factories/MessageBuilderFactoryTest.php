<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Queue\Factories;

use Dvsa\Olcs\Queue\Factories\MessageBuilderFactory;
use Dvsa\Olcs\Queue\Service\Message\MessageBuilder;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Psr\Container\ContainerInterface;

class MessageBuilderFactoryTest extends TestCase
{
    protected $mockSl;
    protected $sut;

    public function setUp(): void
    {
        $this->mockSl = m::mock(ContainerInterface::class);
        $this->sut = new MessageBuilderFactory();
    }

    public function testCreateService(): void
    {
        $this->assertInstanceOf(MessageBuilder::class, $this->sut->__invoke($this->mockSl, MessageBuilder::class));
    }
}
