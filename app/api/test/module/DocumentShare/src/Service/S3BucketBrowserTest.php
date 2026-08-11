<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\DocumentShare\Service;

use Aws\Command;
use Aws\MockHandler;
use Aws\Result;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Dvsa\Olcs\DocumentShare\Data\Object\File;
use Dvsa\Olcs\DocumentShare\Service\S3BucketBrowser;
use GuzzleHttp\Psr7\Response as Psr7Response;
use GuzzleHttp\Psr7\Utils;
use InvalidArgumentException;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Log\LoggerInterface;

#[\PHPUnit\Framework\Attributes\CoversClass(S3BucketBrowser::class)]
final class S3BucketBrowserTest extends MockeryTestCase
{
    private const string BUCKET = 'test-bucket';

    private MockHandler $mockHandler;

    private function createSut(string $rootPrefix = '', string $bucket = self::BUCKET): S3BucketBrowser
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

        return new S3BucketBrowser($s3, $bucket, $rootPrefix, $logger);
    }

    public function testListReturnsFoldersAndObjects(): void
    {
        $sut = $this->createSut();
        $this->mockHandler->append(new Result([
            'CommonPrefixes' => [['Prefix' => 'documents/LICENSING/'], ['Prefix' => 'documents/PSV/']],
            'Contents' => [
                ['Key' => 'documents/readme.txt', 'Size' => 12, 'LastModified' => '2024-06-01T12:00:00Z'],
            ],
            'NextContinuationToken' => 'tok123',
            'IsTruncated' => true,
        ]));

        $result = $sut->listByPrefix('documents/');

        $this->assertSame(['documents/LICENSING/', 'documents/PSV/'], $result['folders']);
        $this->assertCount(1, $result['objects']);
        $this->assertSame('documents/readme.txt', $result['objects'][0]['key']);
        $this->assertSame(12, $result['objects'][0]['size']);
        $this->assertSame('tok123', $result['nextContinuationToken']);
        $this->assertTrue($result['isTruncated']);

        $cmd = $this->mockHandler->getLastCommand();
        $this->assertSame('ListObjectsV2', $cmd->getName());
        $this->assertSame(self::BUCKET, $cmd['Bucket']);
        $this->assertSame('/', $cmd['Delimiter']);
        $this->assertSame('documents/', $cmd['Prefix']);
    }

    public function testListAtRootOmitsPrefixParam(): void
    {
        $sut = $this->createSut();
        $this->mockHandler->append(new Result(['IsTruncated' => false]));

        $result = $sut->listByPrefix('');

        $this->assertSame([], $result['folders']);
        $this->assertSame([], $result['objects']);
        $this->assertNull($result['nextContinuationToken']);
        $this->assertFalse($result['isTruncated']);
        $this->assertArrayNotHasKey('Prefix', $this->mockHandler->getLastCommand()->toArray());
    }

    public function testListPassesContinuationToken(): void
    {
        $sut = $this->createSut();
        $this->mockHandler->append(new Result(['IsTruncated' => false]));

        $sut->listByPrefix('documents/', 'tok-abc');

        $this->assertSame('tok-abc', $this->mockHandler->getLastCommand()['ContinuationToken']);
    }

    public function testListSkipsFolderPlaceholderKey(): void
    {
        $sut = $this->createSut();
        $this->mockHandler->append(new Result([
            'Contents' => [
                ['Key' => 'documents/', 'Size' => 0, 'LastModified' => '2024-06-01T12:00:00Z'],
                ['Key' => 'documents/real.pdf', 'Size' => 50, 'LastModified' => '2024-06-01T12:00:00Z'],
            ],
            'IsTruncated' => false,
        ]));

        $result = $sut->listByPrefix('documents/');

        $this->assertCount(1, $result['objects']);
        $this->assertSame('documents/real.pdf', $result['objects'][0]['key']);
    }

    public function testListRejectsUnsafePrefix(): void
    {
        $sut = $this->createSut();

        $this->expectException(InvalidArgumentException::class);

        $sut->listByPrefix('../etc/');
    }

    public function testGetObjectReturnsFileWithRawKey(): void
    {
        $sut = $this->createSut();
        $this->mockHandler->append(new Result(['Body' => Utils::streamFor('the-bytes')]));

        $file = $sut->getObject('documents/LICENSING/x.pdf');

        $this->assertInstanceOf(File::class, $file);
        $this->assertSame('the-bytes', $file->getContent());

        $cmd = $this->mockHandler->getLastCommand();
        $this->assertSame('GetObject', $cmd->getName());
        $this->assertSame('documents/LICENSING/x.pdf', $cmd['Key']);
    }

    public function testGetObjectReturnsFalseWhenNotFound(): void
    {
        $sut = $this->createSut();
        $this->mockHandler->append(
            new S3Exception('Not found', new Command('GetObject'), ['code' => 'NoSuchKey', 'response' => new Psr7Response(404)])
        );

        $this->assertFalse($sut->getObject('documents/missing.pdf'));
    }

    public function testGetObjectRejectsUnsafeKey(): void
    {
        $sut = $this->createSut();

        $this->expectException(InvalidArgumentException::class);

        $sut->getObject('/etc/passwd');
    }

    public function testPutObjectStoresObjectAndReturnsOkResponse(): void
    {
        $sut = $this->createSut();
        $this->mockHandler->append(new Result(['ETag' => '"etag"']));

        $file = new File();
        $file->setContent('new-bytes');
        $file->setMimeType('application/pdf');

        $response = $sut->putObject('documents/LICENSING/x.pdf', $file);

        $this->assertTrue($response->isOk());
        $this->assertSame(200, $response->getStatusCode());

        $cmd = $this->mockHandler->getLastCommand();
        $this->assertSame('PutObject', $cmd->getName());
        $this->assertSame(self::BUCKET, $cmd['Bucket']);
        $this->assertSame('documents/LICENSING/x.pdf', $cmd['Key']);
        $this->assertSame('application/pdf', $cmd['ContentType']);
    }

    public function testPutObjectReturnsErrorResponseOnFailure(): void
    {
        $sut = $this->createSut();
        $this->mockHandler->append(
            new S3Exception('Access Denied', new Command('PutObject'), ['code' => 'AccessDenied', 'response' => new Psr7Response(403)])
        );

        $file = new File();
        $file->setContent('new-bytes');

        $response = $sut->putObject('documents/x.pdf', $file);

        $this->assertFalse($response->isSuccess());
        $this->assertSame(500, $response->getStatusCode());
    }

    public function testPutObjectRejectsUnsafeKey(): void
    {
        $sut = $this->createSut();

        $this->expectException(InvalidArgumentException::class);

        $sut->putObject('../evil.pdf', new File());
    }

    public function testListRootsAtConfiguredPrefixAndReturnsRelativeKeys(): void
    {
        $sut = $this->createSut('migration/olcs');
        $this->mockHandler->append(new Result([
            'CommonPrefixes' => [['Prefix' => 'migration/olcs/documents/'], ['Prefix' => 'migration/olcs/templates/']],
            'Contents' => [['Key' => 'migration/olcs/readme.txt', 'Size' => 5, 'LastModified' => null]],
            'IsTruncated' => false,
        ]));

        $result = $sut->listByPrefix('');

        // S3 is queried under the root prefix...
        $this->assertSame('migration/olcs/', $this->mockHandler->getLastCommand()['Prefix']);
        // ...but folders/objects come back relative to it.
        $this->assertSame(['documents/', 'templates/'], $result['folders']);
        $this->assertSame('readme.txt', $result['objects'][0]['key']);
    }

    public function testGetObjectPrependsRoot(): void
    {
        $sut = $this->createSut('migration/olcs');
        $this->mockHandler->append(new Result(['Body' => Utils::streamFor('x')]));

        $sut->getObject('documents/x.pdf');

        $this->assertSame('migration/olcs/documents/x.pdf', $this->mockHandler->getLastCommand()['Key']);
    }

    public function testPutObjectPrependsRoot(): void
    {
        $sut = $this->createSut('migration/olcs');
        $this->mockHandler->append(new Result(['ETag' => '"e"']));

        $file = new File();
        $file->setContent('x');

        $sut->putObject('documents/x.pdf', $file);

        $this->assertSame('migration/olcs/documents/x.pdf', $this->mockHandler->getLastCommand()['Key']);
    }

    public function testRejectsNullByteInKey(): void
    {
        $sut = $this->createSut();

        $this->expectException(InvalidArgumentException::class);

        $sut->getObject("documents/ev\0il.pdf");
    }

    public function testAllowsDoubleDotWithinAFilename(): void
    {
        $sut = $this->createSut();
        $this->mockHandler->append(new Result(['Body' => Utils::streamFor('content')]));

        $file = $sut->getObject('documents/report..final.pdf');

        $this->assertInstanceOf(File::class, $file);
        $this->assertSame('documents/report..final.pdf', $this->mockHandler->getLastCommand()['Key']);
    }

    public function testGetObjectRethrowsNon404S3Errors(): void
    {
        $sut = $this->createSut();
        $this->mockHandler->append(
            new S3Exception('Access Denied', new Command('GetObject'), ['code' => 'AccessDenied', 'response' => new Psr7Response(403)])
        );

        // A permission/transient error must NOT masquerade as "not found" (false); surface it.
        $this->expectException(S3Exception::class);

        $sut->getObject('documents/secret.pdf');
    }

    public function testRejectsControlCharInKey(): void
    {
        $sut = $this->createSut();

        $this->expectException(InvalidArgumentException::class);

        $sut->getObject("documents/ev\ril.pdf");
    }

    public function testGetObjectReturnsEmptyObjectRatherThanNotFound(): void
    {
        $sut = $this->createSut();
        $this->mockHandler->append(new Result(['Body' => Utils::streamFor('')]));

        // A real 0-byte object is listed by the browser, so its download must return the (empty)
        // object, not a misleading "not found".
        $file = $sut->getObject('documents/empty.pdf');

        $this->assertInstanceOf(File::class, $file);
        $this->assertSame('', $file->getContent());
    }

    public function testListByPrefixFailsClosedWhenBucketNotConfigured(): void
    {
        $sut = $this->createSut('', '');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('document_share.s3.bucket');

        $sut->listByPrefix('');
    }

    public function testGetObjectFailsClosedWhenBucketNotConfigured(): void
    {
        $sut = $this->createSut('', '');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('document_share.s3.bucket');

        $sut->getObject('documents/x.pdf');
    }

    public function testPutObjectFailsClosedWhenBucketNotConfigured(): void
    {
        $sut = $this->createSut('', '');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('document_share.s3.bucket');

        $sut->putObject('documents/x.pdf', new File());
    }
}
