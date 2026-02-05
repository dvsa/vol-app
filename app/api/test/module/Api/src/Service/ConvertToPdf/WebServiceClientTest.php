<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\ConvertToPdf;

use Dvsa\Olcs\Api\Service\ConvertToPdf\WebServiceClient;
use Laminas\Http\Client as HttpClient;
use Laminas\Http\Response;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use org\bovigo\vfs\vfsStream;

/**
 * WebServiceClientTest
 */
class WebServiceClientTest extends MockeryTestCase
{
    public function testConvertError(): void
    {
        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('isOk')->with()->once()->andReturn(false);
        $mockResponse->shouldReceive('getBody')->with()->once()->andReturn('BODY');
        $mockResponse->shouldReceive('getReasonPhrase')->with()->once()->andReturn('TEXT');
        $mockResponse->shouldReceive('getStatusCode')->with()->once()->andReturn(500);

        $mockHttpClient = m::mock(HttpClient::class);
        $mockHttpClient->shouldReceive('reset')->with()->once();
        $mockHttpClient->shouldReceive('setMethod')->with('POST')->once();
        $mockHttpClient->shouldReceive('setFileUpload')->with('foo.rtf', 'file')->once();
        $mockHttpClient->shouldReceive('send')->andReturn($mockResponse);

        $this->expectException(
            \Dvsa\Olcs\Api\Domain\Exception\RestResponseException::class
        );
        $sut = new WebServiceClient($mockHttpClient);
        $sut->convert('foo.rtf', 'bar.pdf');
    }

    public function testConvertErrorWithMessage(): void
    {
        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('isOk')->with()->once()->andReturn(false);
        $mockResponse->shouldReceive('getBody')->with()->once()->andReturn('{"Message":"TEXT2"}');
        $mockResponse->shouldReceive('getStatusCode')->with()->once()->andReturn(500);

        $mockHttpClient = m::mock(HttpClient::class);
        $mockHttpClient->shouldReceive('reset')->with()->once();
        $mockHttpClient->shouldReceive('setMethod')->with('POST')->once();
        $mockHttpClient->shouldReceive('setFileUpload')->with('foo.rtf', 'file')->once();
        $mockHttpClient->shouldReceive('send')->andReturn($mockResponse);

        $this->expectException(
            \Dvsa\Olcs\Api\Domain\Exception\RestResponseException::class
        );
        $sut = new WebServiceClient($mockHttpClient);
        $sut->convert('foo.rtf', 'bar.pdf');
    }

    public function testConvert(): void
    {
        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('isOk')->with()->once()->andReturn(true);
        $mockResponse->shouldReceive('getBody')->with()->once()->andReturn('BODY');

        $mockHttpClient = m::mock(HttpClient::class);
        $mockHttpClient->shouldReceive('reset')->with()->once();
        $mockHttpClient->shouldReceive('setMethod')->with('POST')->once();
        $mockHttpClient->shouldReceive('setFileUpload')->with('foo.rtf', 'file')->once();
        $mockHttpClient->shouldReceive('send')->andReturn($mockResponse);

        $vfs = vfsStream::setup('temp');
        $fsFilePath = vfsStream::newFile('file.pdf')->at($vfs)->url();

        $sut = new WebServiceClient($mockHttpClient);
        $sut->convert('foo.rtf', $fsFilePath);

        $this->assertTrue($vfs->hasChild('file.pdf'));
        $this->assertSame('BODY', $vfs->getChild('file.pdf')->getContent());
    }
}
