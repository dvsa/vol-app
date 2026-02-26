<?php

declare(strict_types=1);

namespace OlcsTest\Service\WebDav;

use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\CachingQueryService;
use Common\Service\Cqrs\Response;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Olcs\Service\WebDav\VirtualFile;
use Sabre\DAV\Exception\Forbidden;
use Sabre\DAV\Exception\NotFound;

#[\PHPUnit\Framework\Attributes\CoversClass(VirtualFile::class)]
class VirtualFileTest extends MockeryTestCase
{
    private AnnotationBuilder&MockInterface $annotationBuilder;
    private CachingQueryService&MockInterface $queryService;
    private CommandService&MockInterface $commandService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->annotationBuilder = Mockery::mock(AnnotationBuilder::class);
        $this->queryService = Mockery::mock(CachingQueryService::class);
        $this->commandService = Mockery::mock(CommandService::class);
    }

    private function createSut(string $name = 'test-document.rtf', int $documentId = 42, int $initialSize = 0): VirtualFile
    {
        return new VirtualFile(
            $name,
            $documentId,
            $this->annotationBuilder,
            $this->queryService,
            $this->commandService,
            $initialSize,
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getNameReturnsFilename(): void
    {
        $sut = $this->createSut('my-document.rtf');

        $this->assertEquals('my-document.rtf', $sut->getName());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getContentTypeReturnsCorrectMimeTypeForRtf(): void
    {
        $sut = $this->createSut('document.rtf');

        $this->assertEquals('application/rtf', $sut->getContentType());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getContentTypeReturnsCorrectMimeTypeForDoc(): void
    {
        $sut = $this->createSut('document.doc');

        $this->assertEquals('application/msword', $sut->getContentType());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getContentTypeReturnsCorrectMimeTypeForDocx(): void
    {
        $sut = $this->createSut('document.docx');

        $this->assertEquals(
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            $sut->getContentType()
        );
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getContentTypeReturnsOctetStreamForUnknownExtension(): void
    {
        $sut = $this->createSut('document.xyz');

        $this->assertEquals('application/octet-stream', $sut->getContentType());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function putCallsCommandServiceWithContent(): void
    {
        $sut = $this->createSut('document.rtf', 99);

        $content = 'This is the document content';

        $this->annotationBuilder
            ->shouldReceive('createCommand')
            ->once()
            ->with(Mockery::on(function ($command) use ($content) {
                $data = $command->getArrayCopy();
                return $data['id'] === 99 && $data['content'] === $content;
            }))
            ->andReturnUsing(function ($command) {
                $container = Mockery::mock(\Dvsa\Olcs\Transfer\Command\CommandContainerInterface::class);
                return $container;
            });

        $response = Mockery::mock(Response::class);
        $response->shouldReceive('isOk')->andReturn(true);

        $this->commandService
            ->shouldReceive('send')
            ->once()
            ->andReturn($response);

        $result = $sut->put($content);

        $this->assertNotNull($result);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function putHandlesResourceInput(): void
    {
        $sut = $this->createSut('document.rtf', 99);

        $content = 'Stream content here';
        $stream = fopen('php://temp', 'r+');
        fwrite($stream, $content);
        rewind($stream);

        $this->annotationBuilder
            ->shouldReceive('createCommand')
            ->once()
            ->with(Mockery::on(function ($command) use ($content) {
                $data = $command->getArrayCopy();
                return $data['id'] === 99 && $data['content'] === $content;
            }))
            ->andReturnUsing(function ($command) {
                return Mockery::mock(\Dvsa\Olcs\Transfer\Command\CommandContainerInterface::class);
            });

        $response = Mockery::mock(Response::class);
        $response->shouldReceive('isOk')->andReturn(true);

        $this->commandService
            ->shouldReceive('send')
            ->once()
            ->andReturn($response);

        $result = $sut->put($stream);

        $this->assertNotNull($result);

        if (is_resource($stream)) {
            fclose($stream);
        }
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function putThrowsForbiddenWhenCommandFails(): void
    {
        $sut = $this->createSut('document.rtf', 99);

        $this->annotationBuilder
            ->shouldReceive('createCommand')
            ->once()
            ->andReturnUsing(function ($command) {
                return Mockery::mock(\Dvsa\Olcs\Transfer\Command\CommandContainerInterface::class);
            });

        $response = Mockery::mock(Response::class);
        $response->shouldReceive('isOk')->andReturn(false);
        $response->shouldReceive('getStatusCode')->andReturn(500);

        $this->commandService
            ->shouldReceive('send')
            ->once()
            ->andReturn($response);

        $this->expectException(Forbidden::class);
        $this->expectExceptionMessage('Failed to update document');

        $sut->put('content');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function deleteThrowsForbidden(): void
    {
        $sut = $this->createSut();

        $this->expectException(Forbidden::class);
        $this->expectExceptionMessage('Deleting documents via WebDAV is not permitted');

        $sut->delete();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function setNameThrowsForbidden(): void
    {
        $sut = $this->createSut();

        $this->expectException(Forbidden::class);
        $this->expectExceptionMessage('Renaming documents via WebDAV is not permitted');

        $sut->setName('new-name.rtf');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getSizeReturnsZeroInitiallyWhenNoSizeProvided(): void
    {
        $sut = $this->createSut();

        $this->assertEquals(0, $sut->getSize());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getSizeReturnsInitialSizeFromConstructor(): void
    {
        $sut = $this->createSut(initialSize: 12345);

        $this->assertEquals(12345, $sut->getSize());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getSizeReturnsCachedSizeAfterPut(): void
    {
        $sut = $this->createSut('document.rtf', 99);

        $content = 'Some content';

        $this->annotationBuilder
            ->shouldReceive('createCommand')
            ->once()
            ->andReturnUsing(function ($command) {
                return Mockery::mock(\Dvsa\Olcs\Transfer\Command\CommandContainerInterface::class);
            });

        $response = Mockery::mock(Response::class);
        $response->shouldReceive('isOk')->andReturn(true);

        $this->commandService
            ->shouldReceive('send')
            ->once()
            ->andReturn($response);

        $sut->put($content);

        $this->assertEquals(strlen($content), $sut->getSize());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getETagReturnsStableValue(): void
    {
        $sut = $this->createSut();

        $this->assertStringContainsString('doc-42', $sut->getETag());
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getThrowsNotFoundWhenQueryServiceThrows(): void
    {
        $sut = $this->createSut('document.rtf', 42);

        $this->annotationBuilder
            ->shouldReceive('createQuery')
            ->once()
            ->andReturnUsing(function ($query) {
                return Mockery::mock(\Dvsa\Olcs\Transfer\Query\QueryContainerInterface::class);
            });

        $this->queryService
            ->shouldReceive('send')
            ->once()
            ->andThrow(new \RuntimeException('Connection failed'));

        $this->expectException(NotFound::class);
        $this->expectExceptionMessage('Document download failed');

        $sut->get();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getThrowsNotFoundWhenResponseNotOk(): void
    {
        $sut = $this->createSut('document.rtf', 42);

        $this->annotationBuilder
            ->shouldReceive('createQuery')
            ->once()
            ->andReturnUsing(function ($query) {
                return Mockery::mock(\Dvsa\Olcs\Transfer\Query\QueryContainerInterface::class);
            });

        $response = Mockery::mock(Response::class);
        $response->shouldReceive('isOk')->andReturn(false);
        $response->shouldReceive('getStatusCode')->andReturn(404);

        $this->queryService
            ->shouldReceive('send')
            ->once()
            ->andReturn($response);

        $this->expectException(NotFound::class);
        $this->expectExceptionMessage('Document not found (status: 404)');

        $sut->get();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function getLastModifiedReturnsTimestamp(): void
    {
        $sut = $this->createSut();

        $before = time();
        $result = $sut->getLastModified();

        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual($before, $result);
        $this->assertLessThanOrEqual(time(), $result);
    }
}
