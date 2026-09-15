<?php

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Helper\UrlHelperService;
use Common\Service\Table\Formatter\DocumentDescription;
use Dvsa\Olcs\Utils\Translation\TranslatorDelegator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Document Description Formatter Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class DocumentDescriptionTest extends MockeryTestCase
{
    protected $urlHelper;

    protected $translator;

    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->translator = m::mock(TranslatorDelegator::class);
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->sut = new DocumentDescription($this->translator, $this->urlHelper);
    }

    #[\Override]
    protected function tearDown(): void
    {
        m::close();
    }

    public function testFormat(): void
    {
        // Params
        $data = [
            'documentStoreIdentifier' => 'olbs/tanres01/OLBS02/TanBsDocStore7/2014/08/foo.rtf',
            'description' => 'Foo file',
            'id' => 666,
        ];
        $column = [];

        $this->urlHelper->shouldReceive('fromRoute')
            ->with('getfile', ['identifier' => 666])
            ->andReturn('URL');

        $expected = '<a class="govuk-link" href="URL" >Foo file</a>';
        $this->assertEquals($expected, $this->sut->format($data, $column));
    }

    public function testFormatNoIdentifier(): void
    {
        // Params
        $data = [
            'description' => 'Foo file'
        ];
        $column = [];

        $expected = 'Foo file';
        $this->assertEquals($expected, $this->sut->format($data, $column));
    }

    public function testFormatEmptyIdentifier(): void
    {
        // Params
        $data = [
            'description' => 'Foo file',
            'document_store_id' => '',
        ];
        $column = [];

        $expected = 'Foo file';
        $this->assertEquals($expected, $this->sut->format($data, $column));
    }

    public function testFormatWithFilename(): void
    {
        $data = [
            'description' => null,
            'filename' => '/bar/cake/Foofile.txt'
        ];
        $column = [];

        $expected = 'Foofile.txt';
        $this->assertEquals($expected, $this->sut->format($data, $column));
    }

    public function testFormatWithNoDecriptionNoFilename(): void
    {
        $data = [
            'description' => null,
            'filename' => null
        ];
        $column = [];

        $this->translator
            ->expects('translate')
            ->with('internal.document-description.formatter.no-description')
            ->andReturn('File description missing')
            ->once()
            ->getMock();

        $expected = 'File description missing';
        $this->assertEquals($expected, $this->sut->format($data, $column));
    }

    public function testFormatWithHtml(): void
    {
        // Params
        $data = [
            'documentStoreIdentifier' => 'olbs/tanres01/OLBS02/TanBsDocStore7/2014/08/foo.html',
            'description' => 'Foo file',
            'id' => 666,
        ];
        $column = [];

        $this->urlHelper->shouldReceive('fromRoute')
            ->with('getfile', ['identifier' => 666])
            ->andReturn('URL');

        $expected = '<a class="govuk-link" href="URL" target="_blank">Foo file</a>';
        $this->assertEquals($expected, $this->sut->format($data, $column));
    }

    public function testDescriptionIsEscaped(): void
    {
        $data = [
            'documentStoreIdentifier' => 'olbs/foo.rtf',
            'description' => '<script>alert(document.domain)</script>',
            'id' => 666,
        ];

        $this->urlHelper->shouldReceive('fromRoute')
            ->with('getfile', ['identifier' => 666])
            ->andReturn('URL');

        $expected = '<a class="govuk-link" href="URL" >&lt;script&gt;alert(document.domain)&lt;/script&gt;</a>';
        $this->assertEquals($expected, $this->sut->format($data, []));
    }

    public function testFilenameIsEscaped(): void
    {
        // A slash-free payload: basename() truncates at the last '/', so a payload containing one is
        // incidentally mangled. This one reaches the output intact and must be escaped.
        $data = [
            'description' => null,
            'filename' => '/bar/cake/<img src=x onerror=alert(1)>.txt',
        ];

        $result = $this->sut->format($data, []);

        $this->assertStringNotContainsString('<img', $result);
        $this->assertStringContainsString('&lt;img', $result);
    }
}
