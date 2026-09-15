<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\ConvertToPdf;

use Dvsa\Olcs\Api\Service\ConvertToPdf\GotenbergClient;
use Laminas\Http\Client as HttpClient;
use Laminas\Http\Response;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Service\ConvertToPdf\GotenbergClient::class)]
final class GotenbergClientTest extends MockeryTestCase
{
    public function testConvertHtmlRequestsA4PageSize(): void
    {
        // VOL-7288: without explicit sizing Gotenberg's Chromium renders US Letter
        // (612x792pt) and ignores the template's @page{size:A4}, so the letter body
        // came out a different width from the merged A4 appendix PDFs.
        $tempFile = tempnam(sys_get_temp_dir(), 'gotenberg_test_') . '.pdf';

        $response = m::mock(Response::class);
        $response->shouldReceive('isOk')->andReturn(true);
        $response->shouldReceive('getBody')->andReturn('%PDF-1.4 fake');

        $httpClient = m::mock(HttpClient::class);
        $httpClient->shouldReceive('reset')->once();
        $httpClient->shouldReceive('setUri')->once()->with('http://gotenberg/forms/chromium/convert/html');
        $httpClient->shouldReceive('setMethod')->once();
        $httpClient->shouldReceive('setFileUpload')
            ->once()
            ->with('index.html', 'files', '<html></html>', 'text/html');
        $httpClient->shouldReceive('setParameterPost')
            ->once()
            ->with(m::on(fn(array $params): bool => ($params['preferCssPageSize'] ?? null) === 'true'
                && isset($params['paperWidth'], $params['paperHeight'])));
        $httpClient->shouldReceive('send')->once()->andReturn($response);

        try {
            $sut = new GotenbergClient($httpClient, 'http://gotenberg');
            $sut->convertHtml('<html></html>', $tempFile);

            $this->assertStringEqualsFile($tempFile, '%PDF-1.4 fake');
        } finally {
            if (file_exists($tempFile)) {
                unlink($tempFile);
            }
        }
    }
}
