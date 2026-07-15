<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\DocumentShare\Service;

use Aws\Command;
use Aws\MockHandler;
use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Dvsa\Olcs\DocumentShare\Data\Object\File;
use Dvsa\Olcs\DocumentShare\Service\S3DocumentStore;
use GuzzleHttp\Psr7\Response as Psr7Response;
use GuzzleHttp\Psr7\Utils;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Log\LoggerInterface;

#[\PHPUnit\Framework\Attributes\CoversClass(S3DocumentStore::class)]
final class S3DocumentStoreTest extends MockeryTestCase
{
    private const string BUCKET = 'test-bucket';

    private MockHandler $mockHandler;

    private function createSut(string $keyPrefix = ''): S3DocumentStore
    {
        $this->mockHandler = new MockHandler();
        $s3 = new S3Client([
            'region' => 'eu-west-1',
            'version' => 'latest',
            'credentials' => ['key' => 'test', 'secret' => 'test'],
            'retries' => 0,
            'handler' => $this->mockHandler,
        ]);
        $logger = m::mock(LoggerInterface::class)->shouldIgnoreMissing();

        return new S3DocumentStore($s3, self::BUCKET, $keyPrefix, $logger);
    }

    public function testReadReturnsFileWithContent(): void
    {
        $sut = $this->createSut();
        $this->mockHandler->append(new Result(['Body' => Utils::streamFor('hello world')]));

        $file = $sut->read('documents/x.pdf');

        $this->assertInstanceOf(File::class, $file);
        $this->assertSame('hello world', $file->getContent());

        $cmd = $this->mockHandler->getLastCommand();
        $this->assertSame('GetObject', $cmd->getName());
        $this->assertSame(self::BUCKET, $cmd['Bucket']);
        $this->assertSame('documents/x.pdf', $cmd['Key']);
    }

    public function testReadReturnsFalseWhenObjectNotFound(): void
    {
        $sut = $this->createSut();
        $this->mockHandler->append(
            new S3Exception(
                'Not found',
                new Command('GetObject'),
                ['code' => 'NoSuchKey', 'response' => new Psr7Response(404)]
            )
        );

        $this->assertFalse($sut->read('documents/missing.pdf'));
    }

    public function testReadReturnsFalseWhenObjectEmpty(): void
    {
        $sut = $this->createSut();
        $this->mockHandler->append(new Result(['Body' => Utils::streamFor('')]));

        $this->assertFalse($sut->read('documents/empty.pdf'));
    }

    public function testReadRethrowsNon404S3Errors(): void
    {
        $sut = $this->createSut();
        $this->mockHandler->append(
            new S3Exception(
                'Access Denied',
                new Command('GetObject'),
                ['code' => 'AccessDenied', 'response' => new Psr7Response(403)]
            )
        );

        // 403/5xx must surface as an error, not be swallowed into a false "not found".
        $this->expectException(S3Exception::class);

        $sut->read('documents/secret.pdf');
    }

    public function testReadStripsLeadingSlashFromKey(): void
    {
        $sut = $this->createSut();
        $this->mockHandler->append(new Result(['Body' => Utils::streamFor('x')]));

        $sut->read('/templates/Foo.rtf');

        $this->assertSame('templates/Foo.rtf', $this->mockHandler->getLastCommand()['Key']);
    }

    public function testReadAppliesConfiguredKeyPrefix(): void
    {
        $sut = $this->createSut('olcs');
        $this->mockHandler->append(new Result(['Body' => Utils::streamFor('x')]));

        $sut->read('documents/x.pdf');

        $this->assertSame('olcs/documents/x.pdf', $this->mockHandler->getLastCommand()['Key']);
    }

    public function testWriteStoresObjectAndReturnsSuccessResponse(): void
    {
        $sut = $this->createSut();
        $this->mockHandler->append(new Result(['ETag' => '"etag"']));

        $file = new File();
        $file->setContent('pdf-bytes');
        $file->setMimeType('application/pdf');

        $response = $sut->write('documents/x.pdf', $file);

        $this->assertTrue($response->isSuccess());
        $this->assertSame(200, $response->getStatusCode());

        $cmd = $this->mockHandler->getLastCommand();
        $this->assertSame('PutObject', $cmd->getName());
        $this->assertSame(self::BUCKET, $cmd['Bucket']);
        $this->assertSame('documents/x.pdf', $cmd['Key']);
        $this->assertSame('application/pdf', $cmd['ContentType']);
    }

    public function testWriteReturnsErrorResponseOnFailure(): void
    {
        $sut = $this->createSut();
        $this->mockHandler->append(
            new S3Exception(
                'Access Denied',
                new Command('PutObject'),
                ['code' => 'AccessDenied', 'response' => new Psr7Response(403)]
            )
        );

        $file = new File();
        $file->setContent('pdf-bytes');

        $response = $sut->write('documents/x.pdf', $file);

        $this->assertFalse($response->isSuccess());
        $this->assertSame(500, $response->getStatusCode());
    }

    public function testUpdateOverwritesObjectAndReturnsSuccessResponse(): void
    {
        $sut = $this->createSut('olcs');
        $this->mockHandler->append(new Result(['ETag' => '"etag"']));

        $file = new File();
        $file->setContent('updated-bytes');
        $file->setMimeType('application/rtf');

        $response = $sut->update('documents/x.rtf', $file);

        $this->assertTrue($response->isSuccess());
        $this->assertSame(200, $response->getStatusCode());

        $cmd = $this->mockHandler->getLastCommand();
        $this->assertSame('PutObject', $cmd->getName());
        $this->assertSame(self::BUCKET, $cmd['Bucket']);
        $this->assertSame('olcs/documents/x.rtf', $cmd['Key']);
    }

    public function testRemoveDeletesObjectAndReturnsOkResponse(): void
    {
        $sut = $this->createSut();
        $this->mockHandler->append(new Result([]));

        $response = $sut->remove('documents/x.pdf');

        $this->assertTrue($response->isOk());
        $this->assertSame(200, $response->getStatusCode());

        $cmd = $this->mockHandler->getLastCommand();
        $this->assertSame('DeleteObject', $cmd->getName());
        $this->assertSame(self::BUCKET, $cmd['Bucket']);
        $this->assertSame('documents/x.pdf', $cmd['Key']);
    }

    public function testRemoveReturnsErrorResponseOnFailure(): void
    {
        $sut = $this->createSut();
        $this->mockHandler->append(
            new S3Exception(
                'Boom',
                new Command('DeleteObject'),
                ['code' => 'InternalError', 'response' => new Psr7Response(500)]
            )
        );

        $response = $sut->remove('documents/x.pdf');

        $this->assertFalse($response->isOk());
        $this->assertFalse($response->isNotFound());
        $this->assertSame(500, $response->getStatusCode());
    }
}
