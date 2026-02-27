<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr;

use Aws\S3\S3Client;
use Dvsa\Olcs\Api\Service\Ebsr\S3Processor;
use org\bovigo\vfs\vfsStream;
use Mockery as m;

class S3ProcessorTest extends m\Adapter\Phpunit\MockeryTestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestProcess')]
    public function testProcess(array $s3Options, string $expectedS3Filename): void
    {
        $mockS3Client = m::mock(S3Client::class);
        $mockBucketName = 'testbucket';

        $fileSystem = vfsStream::setup();
        $docName = 'document.xml';
        $identifier = vfsStream::url('root/' . $docName);
        $content = 'doc content';
        $file = vfsStream::newFile($docName);
        $file->setContent($content);
        $fileSystem->addChild($file);

        $mockS3Client->expects('putObject')->with(
            [
                'Bucket' => $mockBucketName,
                'Key' => $expectedS3Filename,
                'Body' => $content
            ]
        )->andReturn(['ObjectURL' => 'testurl']);

        $sut = new S3Processor($mockS3Client, $mockBucketName);
        $this->assertEquals('testurl', $sut->process($identifier, $s3Options));
    }

    public static function dpTestProcess(): array
    {
        return [
            'optional filename provided' => [
                [
                    's3Filename' => 'provided-filename.xml'
                ],
                'provided-filename.xml'
            ],
            'no optional filename provided' => [
                [],
                'document.xml'
            ],
        ];
    }
}
