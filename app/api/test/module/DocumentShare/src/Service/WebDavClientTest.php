<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\DocumentShare\Service;

use Dvsa\Olcs\DocumentShare\Data\Object\File as DsFile;
use Dvsa\Olcs\DocumentShare\Service\WebDavClient as Client;
use Hamcrest\Core\IsTypeOf;
use League\Flysystem\FileExistsException;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Logging\Log\Logger;
use org\bovigo\vfs\vfsStream;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\DocumentShare\Service\WebDavClient::class)]
final class WebDavClientTest extends MockeryTestCase
{
    public const string BASE_URI = 'http://testing';
    public const string WORKSPACE = 'unit_Workspace';

    /** @var  Client */
    protected $sut;

    /** @var  m\MockInterface | FilesystemInterface */
    private $mockFileSystem;

    #[\Override]
    public function setUp(): void
    {
        $this->mockFileSystem = m::mock(FilesystemInterface::class);

        $this->sut = new Client($this->mockFileSystem, $this->createStub(\Psr\Log\LoggerInterface::class));

        $mockFile = m::mock(DsFile::class);

        $logger = m::mock(\Psr\Log\LoggerInterface::class)->shouldIgnoreMissing();

        Logger::setLogger($logger);
    }

    public function testReadSuccess(): void
    {
        $expectContent = 'unit_ABCD1234';
        $testPath = 'test';
        $testStream = fopen('data://text/plain;base64,' . base64_encode($expectContent), 'r');

        $this->mockFileSystem->expects('readStream')->with($testPath)->andReturn($testStream);

        $actual = $this->sut->read($testPath);

        $this->assertInstanceOf(DsFile::class, $actual);
        $this->assertSame($expectContent, file_get_contents($actual->getResource()));
    }

    public function testReadFail(): void
    {
        $testPath = 'test';

        $this->mockFileSystem->expects('readStream')->with($testPath)->andReturn(false);

        $actual = $this->sut->read($testPath);

        $this->assertEquals(false, $actual);
    }

    public function testReadFileNotFound(): void
    {
        $testPath = 'test';

        $this->mockFileSystem->expects('readStream')->with($testPath)->andThrow(new FileNotFoundException($testPath));

        $actual = $this->sut->read($testPath);
        $this->assertEquals(false, $actual);
    }

    public function testWriteSuccess(): void
    {
        $expectPath = 'unit_Path';
        $expectContent = 'unit_ABCDE123';

        $res = vfsStream::newFile('res')
            ->withContent($expectContent)
            ->at(vfsStream::setup('temp'))
            ->url();

        /** @var DsFile $mockFile */
        $mockFile = m::mock(DsFile::class)
            ->shouldReceive('getResource')->andReturn($res)
            ->getMock();

        $this->mockFileSystem->expects('writeStream')->with($expectPath, new IsTypeOf('resource'))->andReturn(true);

        $actual = $this->sut->write($expectPath, $mockFile);

        $this->assertEquals(true, $actual->isSuccess());
    }

    public function testWriteFail(): void
    {
        $expectPath = 'unit_Path';
        $expectContent = 'unit_ABCDE123';

        $res = vfsStream::newFile('res')
            ->withContent($expectContent)
            ->at(vfsStream::setup('temp'))
            ->url();

        /** @var DsFile $mockFile */
        $mockFile = m::mock(DsFile::class)
            ->shouldReceive('getResource')->andReturn($res)
            ->getMock();

        $this->mockFileSystem->expects('writeStream')->with($expectPath, new IsTypeOf('resource'))->andReturn(false);

        $actual = $this->sut->write($expectPath, $mockFile);

        $this->assertEquals(false, $actual->isSuccess());
    }

    public function testWriteFileAlreadyExists(): void
    {
        $expectPath = 'unit_Path';
        $expectContent = 'unit_ABCDE123';

        $res = vfsStream::newFile('res')
            ->withContent($expectContent)
            ->at(vfsStream::setup('temp'))
            ->url();

        /** @var DsFile $mockFile */
        $mockFile = m::mock(DsFile::class)
            ->shouldReceive('getResource')->andReturn($res)
            ->getMock();

        $this->mockFileSystem->expects('writeStream')->with($expectPath, new IsTypeOf('resource'))->andThrow(
            new FileExistsException($expectPath)
        );

        $actual = $this->sut->write($expectPath, $mockFile);

        $this->assertEquals(false, $actual->isSuccess());
    }

    public function testUpdateSuccess(): void
    {
        $expectPath = 'unit_Path';

        $res = vfsStream::newFile('res')
            ->withContent('unit_ABCDE123')
            ->at(vfsStream::setup('temp'))
            ->url();

        /** @var DsFile $mockFile */
        $mockFile = m::mock(DsFile::class)
            ->shouldReceive('getResource')->andReturn($res)
            ->getMock();

        $this->mockFileSystem->expects('updateStream')->with($expectPath, new IsTypeOf('resource'))->andReturn(true);

        $actual = $this->sut->update($expectPath, $mockFile);

        $this->assertTrue($actual->isSuccess());
    }

    public function testUpdateFail(): void
    {
        $expectPath = 'unit_Path';

        $res = vfsStream::newFile('res')
            ->withContent('unit_ABCDE123')
            ->at(vfsStream::setup('temp'))
            ->url();

        /** @var DsFile $mockFile */
        $mockFile = m::mock(DsFile::class)
            ->shouldReceive('getResource')->andReturn($res)
            ->getMock();

        $this->mockFileSystem->expects('updateStream')->with($expectPath, new IsTypeOf('resource'))->andReturn(false);

        $actual = $this->sut->update($expectPath, $mockFile);

        $this->assertFalse($actual->isSuccess());
    }

    public function testUpdateFileNotFound(): void
    {
        $expectPath = 'unit_Path';

        $res = vfsStream::newFile('res')
            ->withContent('unit_ABCDE123')
            ->at(vfsStream::setup('temp'))
            ->url();

        /** @var DsFile $mockFile */
        $mockFile = m::mock(DsFile::class)
            ->shouldReceive('getResource')->andReturn($res)
            ->getMock();

        $this->mockFileSystem->expects('updateStream')->with($expectPath, new IsTypeOf('resource'))->andThrow(
            new FileNotFoundException($expectPath)
        );

        $actual = $this->sut->update($expectPath, $mockFile);

        $this->assertFalse($actual->isSuccess());
    }

    public function testRemoveSuccess(): void
    {
        $this->mockFileSystem->expects('delete')->with('testFileToUnlink')->andReturn(true);

        $result = $this->sut->remove('testFileToUnlink');

        $this->assertTrue($result->isOk());
        $this->assertSame(200, $result->getStatusCode());
    }

    public function testRemoveFail(): void
    {
        $this->mockFileSystem->expects('delete')->with('testFileToUnlink')->andReturn(false);

        $result = $this->sut->remove('testFileToUnlink');

        $this->assertFalse($result->isSuccess());
        $this->assertSame(500, $result->getStatusCode());
    }

    public function testRemoveFileNotFound(): void
    {
        $this->mockFileSystem->expects('delete')->with('testFileToUnlink')->andThrow(
            new FileNotFoundException('test')
        );

        $result = $this->sut->remove('testFileToUnlink');

        $this->assertTrue($result->isNotFound());
        $this->assertSame(404, $result->getStatusCode());
    }
}
