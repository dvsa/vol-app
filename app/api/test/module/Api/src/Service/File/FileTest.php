<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\File;

use Dvsa\Olcs\Api\Service\File\File;
use org\bovigo\vfs\vfsStream;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Service\File\File::class)]
class FileTest extends \PHPUnit\Framework\TestCase
{
    /** @var  File */
    private $sut;

    public function setUp(): void
    {
        $this->sut = new File();
    }

    public function testGetSet(): void
    {
        $identifier = 'unit_Identifier';
        static::assertEquals($this->sut, $this->sut->setIdentifier($identifier));
        static::assertEquals($identifier, $this->sut->getIdentifier());

        $path = 'unit_Path';
        static::assertEquals($this->sut, $this->sut->setPath($path));
        static::assertEquals($path, $this->sut->getPath());

        $name = 'unit_Name.extX';
        static::assertEquals($this->sut, $this->sut->setName($name));
        static::assertEquals($name, $this->sut->getName());

        $content = '<html></html>';
        static::assertEquals(
            $this->sut,
            $this->sut->setContent($content)
        );
        static::assertEquals($content, $this->sut->getContent());
        static::assertEquals('text/html', $this->sut->getMimeType());
        static::assertEquals(13, $this->sut->getSize());
    }

    public function testSetContentFromFileData(): void
    {
        $content = 'test content inside test file';

        $vfs = vfsStream::setup('temp');
        $fsFilePath = vfsStream::newFile('unitTemp')
            ->withContent($content)
            ->at($vfs)
            ->url();

        $file = new File();
        $file->setContent(
            [
                'tmp_name' => $fsFilePath,
            ]
        );

        static::assertEquals($content, $file->getContent());
    }
}
