<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Document;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Service\File\ContentStoreFileUploader;
use Dvsa\Olcs\DocumentShare\Data\Object\File as ContentStoreFile;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use org\bovigo\vfs\vfsStream;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\QueryHandler\Document\AbstractDownload::class)]
final class AbstractDownloadTest extends QueryHandlerTestCase
{
    public const string MIME_TYPE = 'unit_mime';
    public const string MIME_TYPE_EXCLUDE = 'unit_EXC_mime';

    /** @var  AbstractDownloadStub */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new AbstractDownloadStub();

        $this->mockedSmServices['config'] = [
            'document_share' => [
                'invalid_defined_mime_types' => [
                    'unit_excl_ext' => self::MIME_TYPE_EXCLUDE,
                ],
            ],
        ];
        $this->mockedSmServices['FileUploader'] = m::mock(ContentStoreFileUploader::class);

        parent::setUp();
    }

    public function testDownloadFailExcNotFound(): void
    {
        $this->expectException(NotFoundException::class);

        $path = '/unit_dir/unit_file1.pdf';

        $this->mockedSmServices['FileUploader']
            ->shouldReceive('download')
            ->once()
            ->with($path)
            ->andReturnFalse();

        $this->sut->download($path);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestDownload')]
    public function testDownload(mixed $identifier, mixed $path, mixed $isInline, mixed $chosenFileName, mixed $expect): void
    {
        $this->sut->setIsInline($isInline);

        $expectContent = 'unit_Contnet';
        $expectSize = '9999';

        $vfs = vfsStream::setup('temp');
        $tmpFilePath = vfsStream::newFile('stream')->withContent($expectContent)->at($vfs)->url();

        $mockFile = m::mock(ContentStoreFile::class)
            ->shouldReceive('getResource')->once()->andReturn($tmpFilePath)
            ->shouldReceive('getSize')->once()->andReturn($expectSize)
            ->shouldReceive('getMimeType')
            ->times($expect['mime'] !== self::MIME_TYPE_EXCLUDE ? 1 : 0)
            ->andReturn(self::MIME_TYPE)
            ->getMock();

        $this->mockedSmServices['FileUploader']
            ->shouldReceive('download')
            ->once()
            ->with($expect['path'])
            ->andReturn($mockFile);

        //  call & check
        $actual = $this->sut->download($identifier, $path, $chosenFileName);

        $this->assertInstanceOf(\Laminas\Http\Response\Stream::class, $actual);
        $this->assertEquals($tmpFilePath, $actual->getStreamName());
        $this->assertEquals($expectContent, $actual->getBody());

        // Compared as serialised headers rather than via toArray(): Laminas parses
        // Content-Security-Policy into a directive structure, so toArray() does not show what
        // actually goes on the wire.
        $expectHeaders = [
            'Content-Type: ' . $expect['mime'] . '; charset=UTF-8',
            'Content-Length: ' . $expectSize,
            'Content-Disposition: ' . ($expect['isDownload'] ? 'attachment' : 'inline') .
                ';filename="' . $expect['filename'] . '"',
            'X-Content-Type-Options: nosniff',
        ];

        // The sandbox is only applied to content that can execute in our own origin.
        $isScriptable = in_array(
            strtolower(pathinfo((string)$identifier, PATHINFO_EXTENSION)),
            ['html', 'htm', 'xhtml', 'svg', 'xml'],
            true,
        );
        if ($isScriptable) {
            $expectHeaders[] = 'Content-Security-Policy: sandbox allow-scripts;';
        }

        $this->assertSame(
            $expectHeaders,
            array_map(
                static fn($header) => $header->toString(),
                iterator_to_array($actual->getHeaders()),
            ),
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpScriptableTypes')]
    public function testSandboxAppliedOnlyToContentThatExecutesInOurOrigin(
        string $identifier,
        string $mimeType,
        bool $expectSandbox,
    ): void {
        $headers = $this->downloadHeaders($identifier, $mimeType);

        if ($expectSandbox) {
            $this->assertStringContainsString('Content-Security-Policy: sandbox', $headers);
        } else {
            $this->assertStringNotContainsString('Content-Security-Policy', $headers);
        }

        // nosniff is unconditional — it is what protects the non-scriptable types.
        $this->assertStringContainsString('X-Content-Type-Options: nosniff', $headers);
    }

    public static function dpScriptableTypes(): \Iterator
    {
        yield 'html snapshot' => ['snapshot.html', 'text/html', true];
        yield 'svg can script in origin' => ['image.svg', 'image/svg+xml', true];
        yield 'xml' => ['data.xml', 'application/xml', true];

        // Sandboxing an inline PDF risks breaking the browser's built-in viewer, and PDF script
        // runs in that viewer's sandbox rather than our origin.
        yield 'pdf is not sandboxed' => ['report.pdf', 'application/pdf', false];

        // The bulk of the document store.
        yield 'rtf is not sandboxed' => ['letter.rtf', 'application/rtf', false];
        yield 'jpg is not sandboxed' => ['scan.jpg', 'image/jpeg', false];
        yield 'docx is not sandboxed' => ['form.docx', 'application/vnd.openxmlformats', false];

        // Type is decided by the served MIME type as well as the extension, so a mismatch still
        // gets the sandbox.
        yield 'html mime with odd extension' => ['file.dat', 'text/html', true];
    }

    private function downloadHeaders(string $identifier, string $mimeType): string
    {
        $vfs = vfsStream::setup('temp');
        $tmpFilePath = vfsStream::newFile('stream')->withContent('content')->at($vfs)->url();

        $mockFile = m::mock(ContentStoreFile::class)
            ->shouldReceive('getResource')->withNoArgs()->andReturn($tmpFilePath)
            ->shouldReceive('getSize')->withNoArgs()->andReturn('7')
            ->shouldReceive('getMimeType')->withNoArgs()->andReturn($mimeType)
            ->getMock();

        $this->mockedSmServices['FileUploader']
            ->expects('download')
            ->with($identifier)
            ->andReturn($mockFile);

        return $this->sut->download($identifier)->getHeaders()->toString();
    }

    /**
     * allow-same-origin re-grants the real origin and cancels the sandbox entirely. It is a
     * one-token change that silently removes the protection, so it is asserted against explicitly
     * rather than left to review.
     */
    public function testSandboxNeverGrantsSameOrigin(): void
    {
        $headers = $this->downloadHeaders('snapshot.html', 'text/html');

        $this->assertStringContainsString('Content-Security-Policy: sandbox', $headers);
        $this->assertStringNotContainsString('allow-same-origin', $headers);
    }

    public static function dpTestDownload(): \Iterator
    {
        yield [
            'identifier' => 'unit_file.ext',
            'path' => '/unit_dir/unit_file1.pdf',
            'isInline' => false,
            "chosenFileName" => null,
            'expect' => [
                'mime' => self::MIME_TYPE,
                'isDownload' => true,
                'path' => '/unit_dir/unit_file1.pdf',
                'filename' => 'unit_file.ext',
            ],
        ];
        yield [
            'identifier' => 'unit_file.html',
            'path' => null,
            'isInline' => false,
            "chosenFileName" => null,
            'expect' => [
                'mime' => self::MIME_TYPE,
                'isDownload' => false,
                'path' => 'unit_file.html',
                'filename' => 'unit_file.html',
            ],
        ];
        yield [
            'identifier' => 'dir/dir/unit_file.unit_excl_ext',
            'path' => null,
            'isInline' => false,
            "chosenFileName" => null,
            'expect' => [
                'mime' => self::MIME_TYPE_EXCLUDE,
                'isDownload' => true,
                'path' => 'dir/dir/unit_file.unit_excl_ext',
                'filename' => 'unit_file.unit_excl_ext',
            ],
        ];
        yield [
            'identifier' => 'unit_file.ext',
            'path' => 'unti_path',
            'isInline' => true,
            "chosenFileName" => null,
            'expect' => [
                'mime' => self::MIME_TYPE,
                'isDownload' => false,
                'path' => 'unti_path',
                'filename' => 'unit_file.ext',
            ],
        ];
        yield [
            'identifier' => '/foo/bar',
            'path' => null,
            'isInline' => false,
            "chosenFileName" => null,
            'expect' => [
                'mime' => self::MIME_TYPE,
                'isDownload' => true,
                'path' => '/foo/bar',
                'filename' => 'bar.txt',
            ],
        ];
        yield [
            'identifier' => 'unit_file.ext',
            'path' => '/unit_dir/unit_file1.pdf',
            'isInline' => false,
            "chosenFileName" => 'chosen_filename',
            'expect' => [
                'mime' => self::MIME_TYPE,
                'isDownload' => true,
                'path' => '/unit_dir/unit_file1.pdf',
                'filename' => 'chosen_filename.ext',
            ],
        ];
        yield [
            'identifier' => 'unit_file.html',
            'path' => null,
            'isInline' => false,
            "chosenFileName" => 'chosen_filename',
            'expect' => [
                'mime' => self::MIME_TYPE,
                'isDownload' => false,
                'path' => 'unit_file.html',
                'filename' => 'chosen_filename.html',
            ],
        ];
        yield [
            'identifier' => 'dir/dir/unit_file.unit_excl_ext',
            'path' => null,
            'isInline' => false,
            "chosenFileName" => 'chosen_filename',
            'expect' => [
                'mime' => self::MIME_TYPE_EXCLUDE,
                'isDownload' => true,
                'path' => 'dir/dir/unit_file.unit_excl_ext',
                'filename' => 'chosen_filename.unit_excl_ext',
            ],
        ];
        yield [
            'identifier' => 'unit_file.ext',
            'path' => 'unti_path',
            'isInline' => true,
            "chosenFileName" => 'chosen_filename.txt',
            'expect' => [
                'mime' => self::MIME_TYPE,
                'isDownload' => false,
                'path' => 'unti_path',
                'filename' => 'chosen_filename.txt.ext',
            ],
        ];
        yield [
            'identifier' => '/foo/bar',
            'path' => null,
            'isInline' => false,
            "chosenFileName" => 'chosen_filename',
            'expect' => [
                'mime' => self::MIME_TYPE,
                'isDownload' => true,
                'path' => '/foo/bar',
                'filename' => 'chosen_filename.txt',
            ],
        ];
    }
}
