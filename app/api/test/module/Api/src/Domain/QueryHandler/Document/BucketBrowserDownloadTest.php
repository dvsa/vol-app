<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Document;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\QueryHandler\Document\BucketBrowserDownload;
use Dvsa\Olcs\DocumentShare\Data\Object\File;
use Dvsa\Olcs\DocumentShare\Service\S3BucketBrowser;
use Dvsa\Olcs\Transfer\Query\Document\BucketBrowserDownload as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Laminas\Http\Response\Stream;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

final class BucketBrowserDownloadTest extends QueryHandlerTestCase
{
    /** @var m\MockInterface|S3BucketBrowser */
    private $mockBrowser;

    public function setUp(): void
    {
        $this->sut = new BucketBrowserDownload();
        $this->mockBrowser = m::mock(S3BucketBrowser::class);

        $this->mockedSmServices = [
            S3BucketBrowser::class => $this->mockBrowser,
            AuthorizationService::class => m::mock(AuthorizationService::class),
        ];
        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity->getUser->getId')->andReturn(1);

        parent::setUp();
    }

    public function testDownloadReturnsStreamResponse(): void
    {
        $file = new File();
        $file->setContent('the-bytes');
        $file->setMimeType('application/pdf');

        $this->mockBrowser->shouldReceive('getObject')->with('documents/report.pdf')->once()->andReturn($file);

        $response = $this->sut->handleQuery(Qry::create(['key' => 'documents/report.pdf']));

        $this->assertInstanceOf(Stream::class, $response);
        $this->assertSame(200, $response->getStatusCode());

        $headers = $response->getHeaders();
        $this->assertStringContainsString('attachment', $headers->get('Content-Disposition')->getFieldValue());
        $this->assertStringContainsString('report.pdf', $headers->get('Content-Disposition')->getFieldValue());
        $this->assertStringContainsString('application/pdf', $headers->get('Content-Type')->getFieldValue());
    }

    public function testDownloadThrowsNotFoundWhenObjectMissing(): void
    {
        $this->mockBrowser->shouldReceive('getObject')->with('documents/missing.pdf')->once()->andReturn(false);

        $this->expectException(NotFoundException::class);

        $this->sut->handleQuery(Qry::create(['key' => 'documents/missing.pdf']));
    }

    public function testDownloadForcesAttachmentAndNosniffEvenWhenInlineRequested(): void
    {
        $file = new File();
        $file->setContent('<html><script>alert(1)</script></html>');
        $file->setMimeType('text/html');

        $this->mockBrowser->shouldReceive('getObject')->with('documents/evil.html')->once()->andReturn($file);

        // A raw bucket tool must never render arbitrary objects inline, even if inline is asked for.
        $response = $this->sut->handleQuery(Qry::create(['key' => 'documents/evil.html', 'isInline' => true]));

        $headers = $response->getHeaders();
        $this->assertStringStartsWith('attachment', $headers->get('Content-Disposition')->getFieldValue());
        $this->assertStringNotContainsString('inline', (string) $headers->get('Content-Disposition')->getFieldValue());
        $this->assertSame('nosniff', $headers->get('X-Content-Type-Options')->getFieldValue());
    }

    public function testDownloadSanitisesUnsafeCharactersInFilename(): void
    {
        $file = new File();
        $file->setContent('x');
        $file->setMimeType('application/pdf');

        $this->mockBrowser->shouldReceive('getObject')->with('documents/ev"il.pdf')->once()->andReturn($file);

        $response = $this->sut->handleQuery(Qry::create(['key' => 'documents/ev"il.pdf']));

        // The quoted filename must not carry a raw double-quote that breaks out of the header value.
        $disposition = $response->getHeaders()->get('Content-Disposition')->getFieldValue();
        $this->assertStringContainsString('filename="ev_il.pdf"', (string) $disposition);
        $this->assertStringContainsString("filename*=UTF-8''", (string) $disposition);
    }
}
