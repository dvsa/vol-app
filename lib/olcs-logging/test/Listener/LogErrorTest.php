<?php

namespace OlcsTest\Logging\Listener;

use Laminas\EventManager\EventManagerInterface;
use Laminas\Mvc\MvcEvent;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Logging\Helper\LogException;
use Olcs\Logging\Listener\LogError;
use Olcs\Logging\Log\Processor\RequestId;
use Psr\Container\ContainerInterface;

class LogErrorTest extends TestCase
{
    public function testAttach(): void
    {
        $sut = new LogError();

        $mockEvents = m::mock(EventManagerInterface::class);
        $mockEvents->shouldReceive('attach')->atLeast()->once()
            ->with(MvcEvent::EVENT_DISPATCH_ERROR, [$sut, 'onDispatchError'], 0);
        $mockEvents->shouldReceive('attach')->atLeast()->once()
            ->with(MvcEvent::EVENT_RENDER_ERROR, [$sut, 'onDispatchError'], 0);

        $sut->attach($mockEvents);
    }

    public function testInvoke(): void
    {
        $mockHelper = m::mock(LogException::class);

        $mockRequestId = m::mock(RequestId::class);
        $mockRequestId->shouldReceive('getIdentifier')->once()->andReturn('IDENTIFIER');

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with(LogException::class)->andReturn($mockHelper);
        $mockSl->shouldReceive('get')->with(RequestId::class)->andReturn($mockRequestId);

        $sut = new LogError();
        $service = $sut->__invoke($mockSl, LogError::class);

        $this->assertSame($sut, $service);
        $this->assertSame($mockHelper, $service->getLogExceptionHelper());
        $this->assertSame('IDENTIFIER', $service->getIdentifier());
    }

    public function testOnDispatchError(): void
    {
        $exception = new \Exception();
        $params = ['controller' => 'index', 'action' => 'index'];

        $mockEvent = m::mock(MvcEvent::class);
        $mockEvent->shouldReceive('getParam')->with('exception')->andReturn($exception);
        $mockEvent->shouldReceive('getParam')->with('exceptionNoLog')->andReturn(null);
        $mockEvent->shouldReceive('getRouteMatch->getParams')->atLeast()->once()->andReturn($params);

        $mockHelper = m::mock(LogException::class);
        $mockHelper->shouldReceive('logException')->with($exception, ['data' => $params]);

        $sut = new LogError();
        $sut->setLogExceptionHelper($mockHelper);

        $sut->onDispatchError($mockEvent);
    }

    public function testOnDispatchErrorNoException(): void
    {
        $mockEvent = m::mock(MvcEvent::class);
        $mockEvent->shouldReceive('getParam')->atLeast()->once()->with('exception')->andReturn(null);
        $mockEvent->shouldReceive('getParam')->with('exceptionNoLog')->andReturn(null);

        $sut = new LogError();
        $sut->onDispatchError($mockEvent);
    }

    public function testOnDispatchExceptionNoLog(): void
    {
        $exception = new \Exception();

        $mockEvent = m::mock(MvcEvent::class);
        $mockEvent->shouldReceive('getParam')->atLeast()->once()->with('exception')->andReturn($exception);
        $mockEvent->shouldReceive('getParam')->with('exceptionNoLog')->andReturn(true);

        $sut = new LogError();
        $sut->onDispatchError($mockEvent);
    }
}
