<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark\Base;

use Dvsa\Olcs\Api\Service\Document\Parser\ParserInterface;
use Dvsa\OlcsTest\Api\Service\Document\Bookmark\Base\Stub\AbstractBookmarkStub;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use org\bovigo\vfs\vfsStream;

/**
 * @covers Dvsa\Olcs\Api\Service\Document\Bookmark\Base\AbstractBookmark
 * @covers Dvsa\Olcs\Api\Service\Document\Bookmark\Base\StaticBookmark
 */
final class AbstractBookmarkTest extends MockeryTestCase
{
    public function testGetSet(): void
    {
        $sut = new AbstractBookmarkStub();

        $expectToken = 'unit_Token';
        $sut->setToken($expectToken);
        $this->assertEquals($expectToken, $sut->getToken());

        $this->assertEquals(AbstractBookmarkStub::PREFORMATTED, $sut->isPreformatted());
        $this->assertTrue($sut->isStatic());

        /** @var ParserInterface $mockParser */
        $mockParser = m::mock(ParserInterface::class);
        $sut->setParser($mockParser);
        $this->assertSame($mockParser, $sut->getParser());
    }

    public function testGetSnipped(): void
    {
        $expectContent = 'unit_FileContent';
        $expectExt = 'ut';

        $vfs = vfsStream::setup('root');
        vfsStream::newFile('AbstractBookmarkStub.' . $expectExt)
            ->withContent($expectContent)
            ->at($vfs);

        /** @var ParserInterface $mockParser */
        $mockParser = m::mock(ParserInterface::class)
            ->shouldReceive('getFileExtension')->once()->andReturn($expectExt)
            ->getMock();

        $sut = new AbstractBookmarkStub();
        $sut->setParser($mockParser);
        $sut->setSnippetPath($vfs->url() . '/');

        $this->assertEquals($expectContent, $sut->getSnippet());
    }
}
