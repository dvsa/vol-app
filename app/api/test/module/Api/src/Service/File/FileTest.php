<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\File;

use Dvsa\Olcs\Api\Service\File\File;
use org\bovigo\vfs\vfsStream;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Service\File\File::class)]
final class FileTest extends \PHPUnit\Framework\TestCase
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
        $this->assertEquals($this->sut, $this->sut->setIdentifier($identifier));
        $this->assertEquals($identifier, $this->sut->getIdentifier());

        $path = 'unit_Path';
        $this->assertEquals($this->sut, $this->sut->setPath($path));
        $this->assertEquals($path, $this->sut->getPath());

        $name = 'unit_Name.extX';
        $this->assertEquals($this->sut, $this->sut->setName($name));
        $this->assertEquals($name, $this->sut->getName());

        $content = '<html></html>';
        $this->assertEquals($this->sut, $this->sut->setContent($content));
        $this->assertEquals($content, $this->sut->getContent());
        $this->assertEquals('text/html', $this->sut->getMimeType());
        $this->assertEquals(13, $this->sut->getSize());
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

        $this->assertEquals($content, $file->getContent());
    }
}
