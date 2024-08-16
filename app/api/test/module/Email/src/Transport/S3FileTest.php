<?php

namespace Dvsa\OlcsTest\Email\Transport;

use Aws\Command;
use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Email\Transport\MultiTransport;
use Dvsa\Olcs\Email\Transport\MultiTransportOptions;
use Dvsa\Olcs\Email\Transport\S3File;
use Dvsa\Olcs\Email\Transport\S3FileOptions;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use org\bovigo\vfs\vfsStream;
use Laminas\Mail\Message;
use Laminas\Mail\Transport\Exception\RuntimeException;
use Laminas\Mail\Transport\File;
use Laminas\Mail\Transport\Sendmail;
use Mockery as m;

/**
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class S3FileTest extends MockeryTestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    protected $path;

    public function setUp(): void
    {
        $this->path = vfsStream::setup('path');
    }

    public function testSendSuccess()
    {
        vfsStream::newFile('example')->at($this->path)->withContent('~');
        $mockMessage = m::mock(Message::class);
        $mockMessage->shouldReceive('getSubject')->with()->once()->andReturn('__-TEST %$£%SUBJECT*&&^');
        $file = $this->path->url() . "/example";
        $mockFileTransport = m::mock(File::class);
        $mockFileTransport->shouldReceive('send')->with($mockMessage)->once();
        $mockFileTransport->shouldReceive('getLastFile')->with()->once()->andReturn(
            $file
        );

        $mockS3Client = m::mock(S3Client::class);
        $mockS3Client->shouldReceive('putObject')->once()->with([
            'Bucket' => 'testBucket',
            'Key' => 'testKey/TEST_SUBJECT',
            'Body' => '~'
        ])->andReturnSelf();
        $mockOptions = m::mock(S3FileOptions::class);
        $mockOptions->shouldReceive('getS3Client')->andReturn($mockS3Client);
        $mockOptions->shouldReceive('getS3Bucket')->andReturn('testBucket');
        $mockOptions->shouldReceive('getS3Key')->andReturn('testKey');
        $mockOptions->shouldReceive('getAwsOptions')->andReturn(['region' => 'test', "version" => 'latest']);

        $sut = m::mock(S3File::class, [$mockFileTransport])->makePartial()->shouldAllowMockingProtectedMethods();
        $sut->setOptions($mockOptions);

        $sut->shouldReceive('deleteFile')->with($file)->once();

        $sut->send($mockMessage);
    }

    public function testSendFail()
    {
        vfsStream::newFile('example')->at($this->path)->withContent('~');
        $mockMessage = m::mock(Message::class);
        $mockMessage->shouldReceive('getSubject')->with()->once()->andReturn('__-TEST %$£%SUBJECT*&&^');
        $file = $this->path->url() . "/example";
        $mockFileTransport = m::mock(File::class);
        $mockFileTransport->shouldReceive('send')->with($mockMessage)->once();
        $mockFileTransport->shouldReceive('getLastFile')->with()->once()->andReturn(
            $file
        );

        $mockS3Client = m::mock(S3Client::class);
        $mockS3Client->shouldReceive('putObject')->once()->with(
            [
                'Bucket' => 'testBucket',
                'Key' => 'testKey/TEST_SUBJECT',
                'Body' => '~'
            ]
        )->andThrow(
            new S3Exception('test', new Command('test'))
        );
        $mockOptions = m::mock(S3FileOptions::class);
        $mockOptions->shouldReceive('getS3Client')->andReturn($mockS3Client);
        $mockOptions->shouldReceive('getS3Bucket')->andReturn('testBucket');
        $mockOptions->shouldReceive('getS3Key')->andReturn('testKey');
        $mockOptions->shouldReceive('getAwsOptions')->andReturn(['region' => 'test', "version" => 'latest']);

        $sut = m::mock(S3File::class, [$mockFileTransport])->makePartial()->shouldAllowMockingProtectedMethods();
        $sut->setOptions($mockOptions);

        $sut->shouldReceive('deleteFile')->with($file)->once();

        $this->expectException(RuntimeException::class);

        $sut->send($mockMessage);
    }
}
