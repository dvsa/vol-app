<?php

/**
 * Queue Processor Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace CliTest\Service\Queue;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Cli\Service\Queue\QueueProcessor;

/**
 * Queue Processor Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class QueueProcessorTest extends MockeryTestCase
{
    protected $sut;
    protected $sm;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->sut = new QueueProcessor();
        $this->sut->setServiceLocator($this->sm);
    }

    public function testProcessNextItemWithoutItem()
    {
        $type = 'Foo';

        // Mocks
        $mockQueue = m::mock();
        $this->sm->setService('Entity\Queue', $mockQueue);

        // Expectations
        $mockQueue->shouldReceive('getNextItem')
            ->once()
            ->with('Foo')
            ->andReturn(null);

        // Assertions
        $this->assertNull($this->sut->processNextItem($type));
    }

    public function testProcessNextItem()
    {
        $type = 'Foo';
        $item = [
            'type' => [
                'id' => 'foo_bar'
            ]
        ];

        // Mocks
        $mockQueue = m::mock();
        $mockMsm = m::mock('\Cli\Service\Queue\MessageConsumerManager')->makePartial();
        $this->sm->setService('Entity\Queue', $mockQueue);
        $this->sm->setService('MessageConsumerManager', $mockMsm);
        $mockConsumer = m::mock('\Cli\Service\Queue\Consumer\MessageConsumerInterface');
        $mockMsm->setService('foo_bar', $mockConsumer);

        // Expectations
        $mockQueue->shouldReceive('getNextItem')
            ->once()
            ->with('Foo')
            ->andReturn($item);

        $mockConsumer->shouldReceive('processMessage')
            ->once()
            ->with($item)
            ->andReturn('foo');

        // Assertions
        $this->assertEquals('foo', $this->sut->processNextItem($type));
    }
}
