<?php

/**
 * VehicleDiscNo formatter test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\VehicleDiscNo;

/**
 * VehicleDiscNo formatter test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class VehicleDiscNoTest extends \PHPUnit\Framework\TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, new VehicleDiscNo()->format($data));
    }

    /**
     * @return \Iterator<(int | string), array<(array<(array<array<(int | string | null)>> | string | null)> | string)>>
     *
     * @psalm-return list{list{array{specifiedDate: null, removalDate: null}, 'Pending'}, list{array{specifiedDate: '2015-01-01', removalDate: null}, ''}, list{array{specifiedDate: null, removalDate: '2015-01-01'}, ''}, list{array{specifiedDate: '2015-01-01', removalDate: '2015-01-01'}, ''}, list{array{specifiedDate: '2015-01-01', removalDate: null, goodsDiscs: list{array{id: 551, discNo: '123456', ceasedDate: null}}}, '123456'}, list{array{specifiedDate: '2015-01-01', removalDate: null, goodsDiscs: list{array{id: 55, discNo: '123456', ceasedDate: '2016-11-15'}, array{id: 4, ceasedDate: '2015-01-01', discNo: 'X111'}}}, ''}, list{array{specifiedDate: '2015-01-01', removalDate: null, goodsDiscs: list{array{id: 55, ceasedDate: null, discNo: null}, array{id: 4, ceasedDate: '2015-01-01', discNo: 'X111'}}}, 'Pending'}, list{array{specifiedDate: '2015-01-01', removalDate: null, goodsDiscs: list{array{id: 55, ceasedDate: null, discNo: 'XX9999'}, array{id: 4, ceasedDate: '2016-11-15', discNo: 'XX1123'}}}, 'XX9999'}}
     */
    public static function provider(): \Iterator
    {
        yield [
            [
                'specifiedDate' => null,
                'removalDate' => null
            ],
            'Pending'
        ];
        yield [
            [
                'specifiedDate' => '2015-01-01',
                'removalDate' => null
            ],
            ''
        ];
        yield [
            [
                'specifiedDate' => null,
                'removalDate' => '2015-01-01'
            ],
            ''
        ];
        yield [
            [
                'specifiedDate' => '2015-01-01',
                'removalDate' => '2015-01-01'
            ],
            ''
        ];
        yield [
            [
                'specifiedDate' => '2015-01-01',
                'removalDate' => null,
                'goodsDiscs' => [
                    [
                        'id' => 551,
                        'discNo' => '123456',
                        'ceasedDate' => null,
                    ]
                ]
            ],
            '123456'
        ];
        yield [
            [
                'specifiedDate' => '2015-01-01',
                'removalDate' => null,
                'goodsDiscs' => [
                    [
                        'id' => 55,
                        'discNo' => '123456',
                        'ceasedDate' => '2016-11-15',
                    ],
                    [
                        'id' => 4,
                        'ceasedDate' => '2015-01-01',
                        'discNo' => 'X111'
                    ]
                ]
            ],
            ''
        ];
        yield [
            [
                'specifiedDate' => '2015-01-01',
                'removalDate' => null,
                'goodsDiscs' => [
                    [
                        'id' => 55,
                        'ceasedDate' => null,
                        'discNo' => null
                    ],
                    [
                        'id' => 4,
                        'ceasedDate' => '2015-01-01',
                        'discNo' => 'X111'
                    ]
                ]
            ],
            'Pending'
        ];
        yield [
            [
                'specifiedDate' => '2015-01-01',
                'removalDate' => null,
                'goodsDiscs' => [
                    [
                        'id' => 55,
                        'ceasedDate' => null,
                        'discNo' => 'XX9999'
                    ],
                    [
                        'id' => 4,
                        'ceasedDate' => '2016-11-15',
                        'discNo' => 'XX1123'
                    ],
                ]
            ],
            'XX9999'
        ];
    }
}
