<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr;

use Dvsa\Olcs\Api\Filesystem\Filesystem;
use Dvsa\Olcs\Api\Service\Ebsr\FileProcessor;
use Dvsa\Olcs\Api\Service\File\File;
use Dvsa\Olcs\Api\Service\File\FileUploaderInterface;
use Laminas\Filter\Decompress;
use Laminas\Filter\Exception\RuntimeException as LaminasFilterRuntimeException;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use org\bovigo\vfs\vfsStream;

/**
 * Class FileProcessorTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr
 */
class FileProcessorTest extends TestCase
{
    public function testFetchXmlFileNameFromDocumentStore(): void
    {
        vfsStream::setup();

        $fileIdentifier = 'ebsr.zip';
        $fileContent = 'contents';
        $tmpDir = '/tmp';
        $extraPath = '/extra/path';
        $tmpEbsrFile = 'ebsr123';
        $extractDir = vfsStream::url('root');
        $xmlFilename = vfsStream::url('root/ebsr.xml');

        touch($xmlFilename);

        $mockFile = m::mock(File::class);
        $mockFile->shouldReceive('getContent')->andReturn($fileContent);

        $mockFileUploader = m::mock(FileUploaderInterface::class);
        $mockFileUploader->shouldReceive('download')->with($fileIdentifier)->andReturn($mockFile);

        $mockFileSystem = m::mock(Filesystem::class);
        $mockFileSystem->shouldReceive('exists')->with($tmpDir . $extraPath)->andReturn(true);
        $mockFileSystem->shouldReceive('createTmpFile')->with($tmpDir . $extraPath, 'ebsr')->andReturn($tmpEbsrFile);
        $mockFileSystem->shouldReceive('dumpFile')->with($tmpEbsrFile, $fileContent);
        $mockFileSystem->shouldReceive('createTmpDir')->with($tmpDir . $extraPath, 'zip')->andReturn($extractDir);

        $mockFilter = m::mock(Decompress::class);
        $mockFilter->shouldReceive('setTarget')->with($extractDir);
        $mockFilter->shouldReceive('filter')->with($tmpEbsrFile);

        $sut = new FileProcessor($mockFileUploader, $mockFileSystem, $mockFilter, $tmpDir);
        $sut->setSubDirPath($extraPath);

        $this->assertEquals(
            $xmlFilename,
            str_replace("\\", "/", $sut->fetchXmlFileNameFromDocumentStore($fileIdentifier))
        );
    }

    public function testFetchXmlFileNameFromDocumentStoreExGt1(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\EbsrPackException::class);

        vfsStream::setup();

        $fileIdentifier = 'ebsr.zip';
        $fileContent = 'contents';
        $tmpDir = '/tmp';
        $tmpEbsrFile = 'ebsr123';
        $extractDir = vfsStream::url('root');
        $xmlFilename = vfsStream::url('root/ebsr.xml');

        touch($xmlFilename);
        touch(vfsStream::url('root/ebsr1.xml'));

        $mockFile = m::mock(File::class);
        $mockFile->shouldReceive('getContent')->andReturn($fileContent);

        $mockFileUploader = m::mock(FileUploaderInterface::class);
        $mockFileUploader->shouldReceive('download')->with($fileIdentifier)->andReturn($mockFile);

        $mockFileSystem = m::mock(Filesystem::class);
        $mockFileSystem->shouldReceive('exists')->with($tmpDir)->andReturn(true);
        $mockFileSystem->shouldReceive('createTmpFile')->with($tmpDir, 'ebsr')->andReturn($tmpEbsrFile);
        $mockFileSystem->shouldReceive('dumpFile')->with($tmpEbsrFile, $fileContent);
        $mockFileSystem->shouldReceive('createTmpDir')->with($tmpDir, 'zip')->andReturn($extractDir);

        $mockFilter = m::mock(Decompress::class);
        $mockFilter->shouldReceive('setTarget')->with($extractDir);
        $mockFilter->shouldReceive('filter')->with($tmpEbsrFile);

        $sut = new FileProcessor($mockFileUploader, $mockFileSystem, $mockFilter, $tmpDir);

        $sut->fetchXmlFileNameFromDocumentStore($fileIdentifier);
    }

    public function testFetchXmlFileNameFromDocumentStoreEx0(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\EbsrPackException::class);

        vfsStream::setup();

        $fileIdentifier = 'ebsr.zip';
        $fileContent = 'contents';
        $tmpDir = '/tmp';
        $tmpEbsrFile = 'ebsr123';
        $extractDir = vfsStream::url('root');

        $mockFile = m::mock(File::class);
        $mockFile->shouldReceive('getContent')->andReturn($fileContent);

        $mockFileUploader = m::mock(FileUploaderInterface::class);
        $mockFileUploader->shouldReceive('download')->with($fileIdentifier)->andReturn($mockFile);

        $mockFileSystem = m::mock(Filesystem::class);
        $mockFileSystem->shouldReceive('exists')->with($tmpDir)->andReturn(true);
        $mockFileSystem->shouldReceive('createTmpFile')->with($tmpDir, 'ebsr')->andReturn($tmpEbsrFile);
        $mockFileSystem->shouldReceive('dumpFile')->with($tmpEbsrFile, $fileContent);
        $mockFileSystem->shouldReceive('createTmpDir')->with($tmpDir, 'zip')->andReturn($extractDir);

        $mockFilter = m::mock(Decompress::class);
        $mockFilter->shouldReceive('setTarget')->with($extractDir);
        $mockFilter->shouldReceive('filter')->with($tmpEbsrFile);

        $sut = new FileProcessor($mockFileUploader, $mockFileSystem, $mockFilter, $tmpDir);

        $sut->fetchXmlFileNameFromDocumentStore($fileIdentifier);
    }

    public function testFetchXmlFileNameFromDocumentStoreWithCorruptZip(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\EbsrPackException::class);

        vfsStream::setup();

        $fileIdentifier = 'ebsr.zip';
        $fileContent = 'contents';
        $tmpDir = '/tmp';
        $tmpEbsrFile = 'ebsr123';
        $extractDir = vfsStream::url('root');
        $exceptionMessage =

        $mockFile = m::mock(File::class);
        $mockFile->shouldReceive('getContent')->andReturn($fileContent);

        $mockFileUploader = m::mock(FileUploaderInterface::class);
        $mockFileUploader->shouldReceive('download')->with($fileIdentifier)->andReturn($mockFile);

        $mockFileSystem = m::mock(Filesystem::class);
        $mockFileSystem->shouldReceive('exists')->with($tmpDir)->andReturn(true);
        $mockFileSystem->shouldReceive('createTmpFile')->with($tmpDir, 'ebsr')->andReturn($tmpEbsrFile);
        $mockFileSystem->shouldReceive('dumpFile')->with($tmpEbsrFile, $fileContent);
        $mockFileSystem->shouldReceive('createTmpDir')->with($tmpDir, 'zip')->andReturn($extractDir);

        $mockFilter = m::mock(Decompress::class);
        $mockFilter->shouldReceive('setTarget')->with($extractDir);
        $mockFilter->shouldReceive('filter')
            ->with($tmpEbsrFile)
            ->andThrow(LaminasFilterRuntimeException::class, $exceptionMessage);

        $sut = new FileProcessor($mockFileUploader, $mockFileSystem, $mockFilter, $tmpDir);

        $sut->fetchXmlFileNameFromDocumentStore($fileIdentifier);
    }

    public function testFetchXmlFileNameFromDocumentStoreMissingTmpDir(): void
    {
        $this->expectException(\RuntimeException::class);

        vfsStream::setup();

        $fileIdentifier = 'ebsr.zip';
        $tmpDir = '/tmp';

        $mockFileUploader = m::mock(FileUploaderInterface::class);

        $mockFileSystem = m::mock(Filesystem::class);
        $mockFileSystem->shouldReceive('exists')->with($tmpDir)->andReturn(false);

        $mockFilter = m::mock(Decompress::class);

        $sut = new FileProcessor($mockFileUploader, $mockFileSystem, $mockFilter, $tmpDir);

        $sut->fetchXmlFileNameFromDocumentStore($fileIdentifier);
    }
}
