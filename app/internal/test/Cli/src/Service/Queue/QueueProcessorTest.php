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

    public function testProcessNextItemWithItemSuccess()
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
        $mockConsumer = m::mock('\Cli\Service\Queue\MessageConsumerInterface');
        $mockMsm->setService('foo_bar', $mockConsumer);

        // Expectations
        $mockQueue->shouldReceive('getNextItem')
            ->once()
            ->with('Foo')
            ->andReturn($item);

        $mockConsumer->shouldReceive('processMessage')
            ->once()
            ->with($item)
            ->andReturn(true)
            ->shouldReceive('processSuccess')
            ->once()
            ->with($item)
            ->andReturn('foo was successful')
            ->shouldReceive('processFailure')
            ->never();

        // Assertions
        $this->assertEquals('foo was successful', $this->sut->processNextItem($type));
    }

    public function testProcessNextItemWithItemFailed()
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
        $mockConsumer = m::mock('\Cli\Service\Queue\MessageConsumerInterface');
        $mockMsm->setService('foo_bar', $mockConsumer);

        // Expectations
        $mockQueue->shouldReceive('getNextItem')
            ->once()
            ->with('Foo')
            ->andReturn($item);

        $mockConsumer->shouldReceive('processMessage')
            ->once()
            ->with($item)
            ->andReturn(false)
            ->shouldReceive('processFailure')
            ->once()
            ->with($item, null)
            ->andReturn('foo failed')
            ->shouldReceive('processSuccess')
            ->never();

        // Assertions
        $this->assertEquals('foo failed', $this->sut->processNextItem($type));
    }

    public function testProcessNextItemWithItemWithException()
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
        $mockConsumer = m::mock('\Cli\Service\Queue\MessageConsumerInterface');
        $mockMsm->setService('foo_bar', $mockConsumer);

        // Expectations
        $mockQueue->shouldReceive('getNextItem')
            ->once()
            ->with('Foo')
            ->andReturn($item);

        $ex = new \Exception('foo');

        $mockConsumer->shouldReceive('processMessage')
            ->once()
            ->with($item)
            ->andThrow($ex)
            ->shouldReceive('processFailure')
            ->once()
            ->with($item, $ex)
            ->andReturn('foo failed')
            ->shouldReceive('processSuccess')
            ->never();

        // Assertions
        $this->assertEquals('foo failed', $this->sut->processNextItem($type));
    }

    /**
     * @dataProvider optionsProvider
     */
    public function testFormatOptions($options, $expected)
    {
        $this->assertEquals($expected, $this->sut->formatOptions($options));
    }

    public function optionsProvider()
    {
        return [
            [
                'foo bar',
                []
            ],
            [
                null,
                []
            ],
            [
                '',
                []
            ],
            [
                '{"foo":"bar"}',
                ['foo' => 'bar']
            ]
        ];
    }
}
