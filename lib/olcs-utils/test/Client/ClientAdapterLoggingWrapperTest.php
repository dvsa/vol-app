<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Utils\Client;

use Dvsa\Olcs\Utils\Client\ClientAdapterLoggingWrapper;
use Laminas\Http\Client;
use Laminas\Uri\Uri;
use Olcs\Logging\Log\Logger;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

final class ClientAdapterLoggingWrapperTest extends TestCase
{
    private ClientAdapterLoggingWrapper $sut;

    /**
     * @var Client\Adapter\Curl&Stub
     */
    private $adapter;

    public function setUp(): void
    {
        $this->adapter = $this->createStub(Client\Adapter\Curl::class);

        $this->sut = new ClientAdapterLoggingWrapper();
        $this->sut->setAdapter($this->adapter);

        Logger::setLogger(new NullLogger());
    }

    public function testSetAdapter()
    {
        $this->assertSame($this->adapter, $this->sut->getAdapter());
    }

    public function testWrapAdapter()
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->once())->method('getAdapter')->willReturn($this->adapter);
        $client->expects($this->once())->method('setAdapter')->with($this->sut);

        $this->sut->wrapAdapter($client);
    }

    public function testSetOptions()
    {
        $adapter = $this->createMock(Client\Adapter\Curl::class);
        $adapter->expects($this->once())->method('setOptions')->with(['foo' => 'bar']);
        $this->sut->setAdapter($adapter);

        $this->sut->setOptions(['foo' => 'bar']);
    }

    public function testConnect()
    {
        $adapter = $this->createMock(Client\Adapter\Curl::class);
        $adapter->expects($this->once())->method('connect')->with('foo.com', 80, false);
        $this->sut->setAdapter($adapter);

        $this->sut->connect('foo.com', 80);
    }

    public function testWrite()
    {
        $adapter = $this->createMock(Client\Adapter\Curl::class);
        $adapter->expects($this->once())->method('write')->with('GET', '/foo', '1.1', [], '');
        $this->sut->setAdapter($adapter);

        $this->sut->write('GET', new Uri('/foo'));
    }

    public function testRead()
    {
        $response = 'HTTP/1.1 200 OK\r\n'
            . 'Date: Mon, 19 Oct 2015 09:23:48 GMT\r\n'
            . 'Server: Apache/2.2.15 (CentOS)\r\n'
            . 'X-Powered-By: PHP/5.5.29\r\n'
            . 'Set-Cookie: PHPSESSID=6aqng9rv62ejn3ijvu2piri865; path=/\r\n'
            . 'Expires: Thu, 19 Nov 1981 08:52:00 GMT\r\n'
            . 'Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0\r\n'
            . 'Pragma: no-cache\r\n'
            . 'Connection: close\r\n'
            . 'Content-Type: application/json; charset=utf-8\r\n'
            . '\r\n'
            . '{\"foo\":\"bar\"}';

        $adapter = $this->createMock(Client\Adapter\Curl::class);
        $adapter->expects($this->once())->method('read')->willReturn($response);
        $this->sut->setAdapter($adapter);

        $this->sut->setShouldLogData(false);

        $this->assertEquals($response, $this->sut->read());
    }

    public function testClose()
    {
        $adapter = $this->createMock(Client\Adapter\Curl::class);
        $adapter->expects($this->once())->method('close');
        $this->sut->setAdapter($adapter);

        $this->sut->close();
    }

    public function testSetOutputStream()
    {
        $stream = stream_context_create();

        $adapter = $this->createMock(Client\Adapter\Curl::class);
        $adapter->expects($this->once())->method('setOutputStream')->willReturn($stream);
        $this->sut->setAdapter($adapter);

        $this->assertSame($this->sut, $this->sut->setOutputStream($stream));
    }
}
