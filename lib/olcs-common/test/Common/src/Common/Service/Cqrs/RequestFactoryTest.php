<?php

namespace CommonTest\Service\Cqrs;

use Common\Service\Cqrs\RequestFactory;
use Laminas\Http\Request;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Logging\Log\Processor\RequestId;
use Psr\Container\ContainerInterface;

class RequestFactoryTest extends TestCase
{
    public function testInvokeWithoutSecureToken(): void
    {
        $cookies = [];

        $mockRequestId = m::mock(RequestId::class);
        $mockRequestId->shouldReceive('getIdentifier')->withNoArgs()->once()->andReturn('IDENT1');

        $mockRequest = m::mock(Request::class);
        $mockRequest->shouldReceive('getCookie')->andReturn($cookies);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('Request')->andReturn($mockRequest);
        $mockSl->shouldReceive('get')->with(RequestId::class)->andReturn($mockRequestId);

        $sut = new RequestFactory();
        $service = $sut->__invoke($mockSl, Request::class);

        $this->assertInstanceOf(Request::class, $service);
        $this->assertEquals(
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'X-Correlation-Id' => 'IDENT1',
            ],
            $service->getHeaders()->toArray()
        );
    }

    public function testInvokeWithSecureToken(): void
    {
        $cookies = ['secureToken' => 'myToken'];

        $mockRequestId = m::mock(RequestId::class);
        $mockRequestId->shouldReceive('getIdentifier')->withNoArgs()->once()->andReturn('IDENT1');

        $mockRequest = m::mock(Request::class);
        $mockRequest->shouldReceive('getCookie')->andReturn($cookies);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('Request')->andReturn($mockRequest);
        $mockSl->shouldReceive('get')->with(RequestId::class)->andReturn($mockRequestId);

        $sut = new RequestFactory();
        $service = $sut->__invoke($mockSl, Request::class);

        $this->assertInstanceOf(Request::class, $service);
        $this->assertEquals(
            [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Cookie' => 'secureToken=myToken',
                'X-Correlation-Id' => 'IDENT1',
            ],
            $service->getHeaders()->toArray()
        );
    }
}
