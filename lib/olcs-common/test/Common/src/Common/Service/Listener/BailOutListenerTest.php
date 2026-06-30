<?php

/**
 * Bail Out Listener Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Listener;

use Common\Exception\BailOutException;
use Common\Service\Listener\BailOutListener;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\EventManager\EventManagerInterface;
use Laminas\Mvc\MvcEvent;

/**
 * Bail Out Listener Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class BailOutListenerTest extends MockeryTestCase
{
    /**
     * @var BailOutListener
     */
    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new BailOutListener();
    }

    public function testAttach(): void
    {
        $events = m::mock(EventManagerInterface::class);

        $events->shouldReceive('attach')
            ->once()
            ->with(MvcEvent::EVENT_DISPATCH_ERROR, [$this->sut, 'onDispatchError'], 1);

        $this->sut->attach($events);
    }

    public function testOnDispatchErrorWithoutBailOutException(): void
    {
        $ex = m::mock(\Exception::class);

        $e = m::mock(MvcEvent::class);

        $e->shouldReceive('getParam')
            ->with('exception')
            ->andReturn($ex);

        $this->assertNull($this->sut->onDispatchError($e));
    }

    public function testOnDispatchError(): void
    {
        $ex = m::mock(BailOutException::class);
        $ex->shouldReceive('getResponse')
            ->andReturn('foo');

        $e = m::mock(MvcEvent::class);
        $e->shouldReceive('setResult')
            ->with('foo')
            ->andReturn('Result');

        $e->shouldReceive('getParam')
            ->with('exception')
            ->andReturn($ex);

        $this->assertEquals('Result', $this->sut->onDispatchError($e));
    }
}
