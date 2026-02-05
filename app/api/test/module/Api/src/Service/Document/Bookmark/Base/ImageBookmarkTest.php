<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark\Base;

use Dvsa\OlcsTest\Api\Service\Document\Bookmark\Base\Stub\ImageBookmarkStub;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Service\Document\Bookmark\Base\ImageBookmark::class)]
class ImageBookmarkTest extends MockeryTestCase
{
    public function testGetImageFail(): void
    {
        $name = 'unit_Name';

        $mockFs = m::mock();
        $mockFs->shouldReceive('read')->andReturn(null);

        /** @var ImageBookmarkStub|m\MockInterface $sut */
        $sut = new ImageBookmarkStub();
        $sut->setFileStore($mockFs);

        //  expect
        static::expectException(
            \RuntimeException::class
        );

        //  call
        $sut->getImage($name);
    }

    public function testGetImageOk(): void
    {
        $name = 'unit_Name';

        $expectWidth = 21;
        $expectHeight = 11;

        // base64 representation of an empty, white 21x11 jpeg
        $expectContent = base64_decode(
            '/9j/4AAQSkZJRgABAQAAAQABAAD/2wBDAAMCAgICAgMCAgIDAwMDBAYEBAQEBAgGBgUGCQgKCgkICQkKDA8' .
            'MCgsOCwkJDRENDg8QEBEQCgwSExIQEw8QEBD/wAALCAALABUBAREA/8QAFQABAQAAAAAAAAAAAAAAAAAAAA' .
            'n/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/9oACAEBAAA/AKpgA//Z'
        );

        $mockFile = m::mock()
            ->shouldReceive('getContent')
            ->withNoArgs()
            ->andReturn($expectContent)
            ->getMock();

        $mockFs = m::mock()
            ->shouldReceive('read')
            ->with('/templates/Image/' . $name . '.jpg')
            ->andReturn($mockFile)
            ->getMock();

        /** @var \Dvsa\Olcs\Api\Service\Document\Parser\ParserInterface|m\MockInterface $mockParser */
        $mockParser = m::mock()
            ->shouldReceive('renderImage')
            ->with($expectContent, $expectWidth, $expectHeight, 'jpeg')
            ->andReturn('EXPECTED')
            ->getMock();

        /** @var ImageBookmarkStub $sut */
        $sut = new ImageBookmarkStub();
        $sut->setParser($mockParser);
        $sut->setFileStore($mockFs);

        static::assertEquals('EXPECTED', $sut->getImage($name));
    }
}
