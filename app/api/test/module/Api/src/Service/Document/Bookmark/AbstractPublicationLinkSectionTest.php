<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query as DomainQry;
use Dvsa\Olcs\Api\Service\Document\Parser\ParserInterface;
use Dvsa\Olcs\Api\Service\Document\Parser\RtfParser;
use Dvsa\OlcsTest\Api\Service\Document\Bookmark\Stub\AbstractPublicationLinkSectionStub;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use org\bovigo\vfs\vfsStream;

/**
 * @covers Dvsa\Olcs\Api\Service\Document\Bookmark\AbstractPublicationLinkSection
 */
class AbstractPublicationLinkSectionTest extends MockeryTestCase
{
    public function testSetGet(): void
    {
        $sut = new AbstractPublicationLinkSectionStub();

        static::assertEquals(
            [
                AbstractPublicationLinkSectionStub::TEST_PUB_TYPE_SECTION => [
                    AbstractPublicationLinkSectionStub::TEST_SECTION_ID,
                    AbstractPublicationLinkSectionStub::PUB_SECTION_18,
                ],
            ],
            $sut->getPubTypeSection()
        );

        $bookmarkSnippets = $sut->getBookmarkSnippets();
        static::assertIsArray($bookmarkSnippets);
    }

    public function testGetBookmarkSnippetsByClass(): void
    {
        $expectExt = 'unit';

        /** @var ParserInterface $mockParser */
        $mockParser = m::mock(ParserInterface::class)
            ->shouldReceive('getFileExtension')->once()->andReturn($expectExt)
            ->getMock();

        $vfs = vfsStream::setup('root');
        vfsStream::newFile('TanTableRow1.' . $expectExt)
            ->withContent('unit_SnippedContent')
            ->at($vfs);
        vfsStream::newFile('PubContentLine.' . $expectExt)
            ->withContent('unit_PubContent')
            ->at($vfs);

        $sut = new AbstractPublicationLinkSectionStub();
        $sut->setParser($mockParser);
        $sut->setSnippetPath($vfs->url() . '/');

        static::assertEquals(
            [
                'unit_SnippedContent',
                'unit_PubContent',
            ],
            $sut->getBookmarkSnippetsByClass('Section33')
        );
    }

    public function testGetQuery(): void
    {
        $sut = new AbstractPublicationLinkSectionStub();

        /** @var DomainQry\Bookmark\PublicationBundle $actual */
        $actual = $sut->getQuery(['publicationId' => 9999]);

        static::assertInstanceOf(DomainQry\Bookmark\PublicationBundle::class, $actual);
        static::assertEquals(9999, $actual->getId());
    }

    public function testRender(): void
    {
        $snippetFiles = [
            'unit_SnippetFile1',
        ];

        /** @var AbstractPublicationLinkSectionStub|m\MockInterface $sut */
        $sut = m::mock(AbstractPublicationLinkSectionStub::class . '[getBookmarkSnippetsByClass]');

        //  set data
        $sut->setData(
            [
                'pubType' => AbstractPublicationLinkSectionStub::TEST_PUB_TYPE_SECTION,
                'publicationLinks' => [
                    [
                        'publicationSection' => [
                            'id' => AbstractPublicationLinkSectionStub::PUB_SECTION_18,
                        ],
                        'text1' => 'unit_Text1',
                        'text2' => 'unit_Text2',
                        'text3' => 'unit_Text3',
                    ],
                    [
                        'publicationSection' => [
                            'id' => AbstractPublicationLinkSectionStub::TEST_SECTION_ID,
                        ],
                        'text1' => 'unit_Text1',
                        'text2' => 'unit_Text2',
                        'text3' => 'unit_Text3',
                    ],
                ],
            ]
        );

        //  mock
        $sut->shouldReceive('getBookmarkSnippetsByClass')
            ->once()
            ->andReturn($snippetFiles);

        //  mock parser
        /** @var RtfParser $mockParser */
        $mockParser = m::mock(RtfParser::class)
            ->shouldReceive('replace')
            ->times(2)
            ->andReturnUsing(
                fn($file, $token) => $file . '_' . implode('|', $token) . '@'
            )
            ->shouldReceive('getEntitiesAndQuote')
            ->times(6)
            ->andReturnUsing(
                fn($text) => $text === null ? '' : $text . 'f'
            )
            ->getMock();

        $sut->setParser($mockParser);

        //  call & check
        $actual = $sut->render();

        static::assertEquals(
            'unit_SnippetFile1_|unit_Text2f|unit_Text1f@' .
            'unit_SnippetFile1_unit_Text1f|unit_Text2f|unit_Text3f@',
            $actual
        );
    }

    public function testRenderNoEntries(): void
    {
        /** @var AbstractPublicationLinkSectionStub|m\MockInterface $sut */
        $sut = m::mock(AbstractPublicationLinkSectionStub::class . '[getPubTypeSection]');

        //  set data
        $sut->setData(
            [
                'pubType' => 'unit_PubSection',
                'publicationLinks' => [],
            ]
        );

        //  mock
        $sut->shouldReceive('getPubTypeSection')
            ->once()
            ->andReturn(['unit_PubSection' => 660]);

        //  call & check
        static::assertEquals('No entries', $sut->render());
    }
}
