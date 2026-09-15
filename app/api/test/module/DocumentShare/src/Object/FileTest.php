<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\DocumentShare\Object;

use Dvsa\Olcs\DocumentShare\Data\Object\File;
use org\bovigo\vfs\vfsStream;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\DocumentShare\Data\Object\File::class)]
final class FileTest extends \PHPUnit\Framework\TestCase
{
    /** @var  \org\bovigo\vfs\vfsStreamDirectory */
    private $vfs;

    public function setUp(): void
    {
        $this->vfs = vfsStream::setup('temp');
    }

    public function testSetGet(): void
    {
        $sut = new File();

        //  check set/get content
        $content = '<html></html>';

        $this->assertEquals($sut, $sut->setContent($content));
        $this->assertEquals($content, $sut->getContent());

        //  check mime
        $this->assertEquals('text/html', $sut->getMimeType());

        //  check size
        $this->assertEquals(strlen($content), $sut->getSize());
    }

    public function testSetResource(): void
    {
        $fsFilePath1 = vfsStream::newFile('unitTemp1')->at($this->vfs)->url();
        $fsFilePath2 = vfsStream::newFile('unitTemp2')->at($this->vfs)->url();

        $sut = new File();

        //  assing resource
        $this->assertEquals($sut, $sut->setResource($fsFilePath1));
        $this->assertEquals($fsFilePath1, $sut->getResource());

        $this->assertTrue(is_file($fsFilePath1));
        $this->assertTrue(is_file($fsFilePath2));

        //  set other resource, previous one should be removed
        $sut->setResource($fsFilePath2);
        $this->assertEquals($fsFilePath2, $sut->getResource());
        $this->assertFalse(is_file($fsFilePath1));
        $this->assertTrue(is_file($fsFilePath2));

        //  check file removed when destroy object
        unset($sut);
        $this->assertFalse(is_file($fsFilePath2));
    }
}
