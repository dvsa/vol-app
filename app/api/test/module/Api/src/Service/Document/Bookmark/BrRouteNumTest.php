<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\BrRouteNum;

/**
 * Br Route Num test
 */
final class BrRouteNumTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new BrRouteNum();

        $this->assertInstanceOf(
            \Dvsa\Olcs\Transfer\Query\QueryInterface::class,
            $bookmark->getQuery(['busRegId' => 123])
        );
        $this->assertNull($bookmark->getQuery([]));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('renderDataProvider')]
    public function testRender(mixed $data, mixed $expected): void
    {
        $bookmark = new BrRouteNum();
        $bookmark->setData($data);

        $this->assertEquals($expected, $bookmark->render());
    }

    public static function renderDataProvider(): \Iterator
    {
        // no results
        yield [
            [],
            ''
        ];
        // service without other services
        yield [
            [
                'serviceNo' => '1'
            ],
            '1'
        ];
        // service without other services
        yield [
            [
                'serviceNo' => '1',
                'otherServices' => []
            ],
            '1'
        ];
        // service with one other service
        yield [
            [
                'serviceNo' => '1',
                'otherServices' => [
                    ['serviceNo' => '2']
                ]
            ],
            '1 (2)'
        ];
        // service with many other services
        yield [
            [
                'serviceNo' => '1',
                'otherServices' => [
                    ['serviceNo' => '2'],
                    ['serviceNo' => '3'],
                    ['serviceNo' => '4'],
                ]
            ],
            '1 (2, 3, 4)'
        ];
    }
}
