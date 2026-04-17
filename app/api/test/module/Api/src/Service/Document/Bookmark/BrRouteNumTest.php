<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\BrRouteNum;

/**
 * Br Route Num test
 */
class BrRouteNumTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new BrRouteNum();

        $this->assertInstanceOf(
            \Dvsa\Olcs\Transfer\Query\QueryInterface::class,
            $bookmark->getQuery(['busRegId' => 123])
        );
        $this->assertTrue(is_null($bookmark->getQuery([])));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('renderDataProvider')]
    public function testRender(mixed $data, mixed $expected): void
    {
        $bookmark = new BrRouteNum();
        $bookmark->setData($data);

        $this->assertEquals($expected, $bookmark->render());
    }

    public static function renderDataProvider(): array
    {
        return [
            // no results
            [
                [],
                ''
            ],
            // service without other services
            [
                [
                    'serviceNo' => '1'
                ],
                '1'
            ],
            // service without other services
            [
                [
                    'serviceNo' => '1',
                    'otherServices' => []
                ],
                '1'
            ],
            // service with one other service
            [
                [
                    'serviceNo' => '1',
                    'otherServices' => [
                        ['serviceNo' => '2']
                    ]
                ],
                '1 (2)'
            ],
            // service with many other services
            [
                [
                    'serviceNo' => '1',
                    'otherServices' => [
                        ['serviceNo' => '2'],
                        ['serviceNo' => '3'],
                        ['serviceNo' => '4'],
                    ]
                ],
                '1 (2, 3, 4)'
            ],
        ];
    }
}
