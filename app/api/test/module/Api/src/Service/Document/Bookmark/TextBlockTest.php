<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query as DomainQry;
use Dvsa\Olcs\Api\Service\Document\Bookmark\TextBlock;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Service\Document\Bookmark\TextBlock::class)]
final class TextBlockTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQueryNull(): void
    {
        $sut = new TextBlock();
        $sut->setToken('unit_Token');

        $this->assertNotInstanceOf(\Dvsa\Olcs\Api\Domain\Query\Bookmark\DocParagraphBundle::class, $sut->getQuery(
            [
                'bookmarks' => [
                    'unit_Token' => null,
                ],
            ]
        ));
    }

    public function testGetQuery(): void
    {
        $sut = new TextBlock();
        $sut->setToken('unit_Token');

        $actual = $sut->getQuery(
            [
                'bookmarks' => [
                    'unit_Token' => [9999, 8888],
                ],
            ]
        );

        $this->assertCount(2, $actual);
        $this->assertInstanceOf(DomainQry\Bookmark\DocParagraphBundle::class, reset($actual));

        /** @var DomainQry\Bookmark\DocParagraphBundle $query */
        $query = $actual[1];
        $this->assertEquals(8888, $query->getId());
    }

    public function testRenderConcatenatesParagraphsWithNewlines(): void
    {
        $bookmark = new TextBlock();
        $bookmark->setData(
            [
                ['paraText' => 'Para 1'],
                ['paraText' => 'Para 2'],
                ['paraText' => 'Para 3']
            ]
        );

        $result = $bookmark->render();

        $this->assertEquals(
            "Para 1\nPara 2\nPara 3",
            $result
        );
    }

    public function testRenderWithStringDataJustReturnsString(): void
    {
        $bookmark = new TextBlock();
        $bookmark->setData('foo bar');

        $result = $bookmark->render();

        $this->assertEquals('foo bar', $result);
    }
}
