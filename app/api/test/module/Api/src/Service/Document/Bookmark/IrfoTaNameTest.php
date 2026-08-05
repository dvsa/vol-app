<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\IrfoTaName as Sut;

/**
 * IrfoTaName test
 */
final class IrfoTaNameTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new Sut();
        $query = $bookmark->getQuery(['organisation' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpRenderValidDataProvider')]
    public function testRender(mixed $results, mixed $expected): void
    {
        $bookmark = new Sut();
        $bookmark->setData($results);

        $this->assertEquals($expected, $bookmark->render());
    }

    public static function dpRenderValidDataProvider(): \Iterator
    {
        yield [
            [
                'tradingNames' => [
                    [
                        'name' => 'Trading Name 1',
                    ],
                    [
                        'name' => 'Trading Name 2',
                        'licence' => [
                            'id' => 10
                        ]
                    ],
                    [
                        'name' => 'Trading Name 3',
                    ],
                    [
                        'name' => 'Trading Name 4',
                    ],
                ]
            ],
            'T/A: Trading Name 1 Trading Name 3 Trading Name 4',
        ];
        yield [
            [
                'tradingNames' => [
                    [
                        'name' => 'Trading Name 2',
                        'licence' => [
                            'id' => 10
                        ]
                    ],
                ]
            ],
            '',
        ];
        yield [
            [
                'tradingNames' => []
            ],
            '',
        ];
    }
}
