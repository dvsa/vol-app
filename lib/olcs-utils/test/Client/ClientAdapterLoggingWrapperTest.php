<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Utils\Client;

use Dvsa\Olcs\Utils\Client\ClientAdapterLoggingWrapper;
use Laminas\Http\Client;
use Laminas\Uri\Uri;
use Olcs\Logging\Log\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

class ClientAdapterLoggingWrapperTest extends TestCase
{
    private ClientAdapterLoggingWrapper $sut;

    private MockObject $mockAdapter;

    public function setUp(): void
    {
        $this->mockAdapter = $this->createMock(Client\Adapter\Curl::class);

        $this->sut = new ClientAdapterLoggingWrapper();
        $this->sut->setAdapter($this->mockAdapter);

        Logger::setLogger(new NullLogger());
    }

    public function testSetAdapter()
    {
        $this->assertSame($this->mockAdapter, $this->sut->getAdapter());
    }

    public function testWrapAdapter()
    {
        $client = $this->createMock(Client::class);
        $client->expects($this->once())->method('getAdapter')->willReturn($this->mockAdapter);
        $client->expects($this->once())->method('setAdapter')->with($this->sut);

        $this->sut->wrapAdapter($client);
    }

    public function testSetOptions()
    {
        $this->mockAdapter->expects($this->once())->method('setOptions')->with(['foo' => 'bar']);

        $this->sut->setOptions(['foo' => 'bar']);
    }

    public function testConnect()
    {
        $this->mockAdapter->expects($this->once())->method('connect')->with('foo.com', 80, false);

        $this->sut->connect('foo.com', 80);
    }

    public function testWrite()
    {
        $this->mockAdapter->expects($this->once())->method('write')->with('GET', '/foo', '1.1', [], '');

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

        $this->mockAdapter->expects($this->once())->method('read')->willReturn($response);

        $this->sut->setShouldLogData(false);

        $this->assertEquals($response, $this->sut->read());
    }

    public function testClose()
    {
        $this->mockAdapter->expects($this->once())->method('close');

        $this->sut->close();
    }

    public function testSetOutputStream()
    {
        $stream = stream_context_create();

        $this->mockAdapter->expects($this->once())->method('setOutputStream')->willReturn($stream);

        $this->assertSame($this->sut, $this->sut->setOutputStream($stream));
    }
}
