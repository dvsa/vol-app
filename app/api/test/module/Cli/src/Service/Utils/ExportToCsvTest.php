<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Service\Utils;

use Dvsa\Olcs\Cli\Service\Utils\ExportToCsv;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Cli\Service\Utils\ExportToCsv::class)]
final class ExportToCsvTest extends MockeryTestCase
{
    /** @var  string */
    private $tmpPath;
    /** @var  string */
    private $fileName;

    #[\Override]
    public function setUp(): void
    {
        $vfs = vfsStream::setup('root');

        $this->tmpPath = $vfs->url() . '/unit';
        $this->fileName = $this->tmpPath . '/unitFileName.tmp';
    }

    public function testOk(): void
    {
        //  call & check
        ExportToCsv::createFile($this->fileName);

        /** @var vfsStreamStructureVisitor $vfsRootDir */
        $vfsRootDir = vfsStream::inspect(new vfsStreamStructureVisitor());
        $this->assertEquals([
            'root' => [
                'unit' => [
                    'unitFileName.tmp' => null,
                ],
            ],
        ], $vfsRootDir->getStructure());
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
