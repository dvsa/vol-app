<?php

namespace Dvsa\OlcsTest\Utils\Client;

use Dvsa\Olcs\Utils\Client\ClientAdapterLoggingWrapper;
use Dvsa\Olcs\Utils\Client\HttpExternalClientFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Laminas\Http\Client;
use Laminas\Http\Client\Adapter\Curl;
use Laminas\Http\Client\Adapter\Socket;

class HttpExternalClientFactoryTest extends TestCase
{
    public function testFactoryNoConfig()
    {
        $sut = new HttpExternalClientFactory();

        $mockSl = $this->createMock(ContainerInterface::class);
        $mockSl->expects($this->once())->method('get')->with('config')->willReturn([]);

        $object = $sut->__invoke($mockSl, Client::class);

        $this->assertInstanceOf(Client::class, $object);
        $this->assertInstanceOf(ClientAdapterLoggingWrapper::class, $object->getAdapter());
        $this->assertInstanceOf(Socket::class, $object->getAdapter()->getAdapter());
    }

    public function testFactoryConfig()
    {
        $sut = new HttpExternalClientFactory();

        $mockSl = $this->createMock(ContainerInterface::class);
        $mockSl
            ->expects($this->once())
            ->method('get')
            ->with('config')
            ->willReturn(['http_external' => ['adapter' => Curl::class]]);

        $object = $sut->__invoke($mockSl, Client::class);

        $this->assertInstanceOf(Client::class, $object);
        $this->assertInstanceOf(ClientAdapterLoggingWrapper::class, $object->getAdapter());
        $this->assertInstanceOf(Curl::class, $object->getAdapter()->getAdapter());
    }
}
