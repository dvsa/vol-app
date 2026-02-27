<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Service\Utils;

use Dvsa\Olcs\Cli\Service\Utils\ExportToCsv;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;

/**
 * @covers Dvsa\Olcs\Cli\Service\Utils\ExportToCsv
 */
class ExportToCsvTest extends MockeryTestCase
{
    /** @var  string */
    private $tmpPath;
    /** @var  string */
    private $fileName;

    /** @var  \org\bovigo\vfs\vfsStreamDirectory */
    private $vfs;

    public function setUp(): void
    {
        $this->vfs = vfsStream::setup('root');

        $this->tmpPath = $this->vfs->url() . '/unit';
        $this->fileName = $this->tmpPath . '/unitFileName.tmp';
    }

    public function testOk(): void
    {
        //  call & check
        ExportToCsv::createFile($this->fileName);

        /** @var vfsStreamStructureVisitor $vfsRootDir */
        $vfsRootDir = vfsStream::inspect(new vfsStreamStructureVisitor());
        static::assertEquals(
            [
                'root' => [
                    'unit' => [
                        'unitFileName.tmp' => null,
                    ],
                ],
            ],
            $vfsRootDir->getStructure()
        );
    }

    public function testExceptionCreateDir(): void
    {
        //  create file with dir name
        $fh = fopen($this->tmpPath, 'w');
        fclose($fh);

        //  expect
        $this->expectException(\Exception::class);

        //  call & check
        ExportToCsv::createFile($this->fileName);
    }

    public function testExceptionCreateFile(): void
    {
        //  create file with dir name
        /** @noinspection MkdirRaceConditionInspection */
        mkdir($this->fileName, 0750, true);

        //  expect
        $this->expectException(\Exception::class);

        //  call & check
        ExportToCsv::createFile($this->fileName);
    }
}
