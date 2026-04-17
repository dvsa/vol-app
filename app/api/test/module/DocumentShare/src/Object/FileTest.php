<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\DocumentShare\Object;

use Dvsa\Olcs\DocumentShare\Data\Object\File;
use org\bovigo\vfs\vfsStream;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\DocumentShare\Data\Object\File::class)]
class FileTest extends \PHPUnit\Framework\TestCase
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

        static::assertEquals($sut, $sut->setContent($content));
        static::assertEquals($content, $sut->getContent());

        //  check mime
        static::assertEquals('text/html', $sut->getMimeType());

        //  check size
        static::assertEquals(strlen($content), $sut->getSize());
    }

    public function testSetResource(): void
    {
        $fsFilePath1 = vfsStream::newFile('unitTemp1')->at($this->vfs)->url();
        $fsFilePath2 = vfsStream::newFile('unitTemp2')->at($this->vfs)->url();

        $sut = new File();

        //  assing resource
        static::assertEquals($sut, $sut->setResource($fsFilePath1));
        static::assertEquals($fsFilePath1, $sut->getResource());

        static::assertTrue(is_file($fsFilePath1));
        static::assertTrue(is_file($fsFilePath2));

        //  set other resource, previous one should be removed
        $sut->setResource($fsFilePath2);
        static::assertEquals($fsFilePath2, $sut->getResource());
        static::assertFalse(is_file($fsFilePath1));
        static::assertTrue(is_file($fsFilePath2));

        //  check file removed when destroy object
        unset($sut);
        static::assertFalse(is_file($fsFilePath2));
    }
}
