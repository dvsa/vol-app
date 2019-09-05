<?php

namespace PermitsTest\Data\Mapper;

use Permits\Data\Mapper\ValidEcmtPermits;

/**
 * ValidEcmtPermitsTest
 */
class ValidEcmtPermitsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider dpTestMapForDisplay
     */
    public function testMapForDisplay($input, $expected)
    {
        self::assertEquals($expected, ValidEcmtPermits::mapForDisplay($input));
    }

    public function dpTestMapForDisplay()
    {
        return [
            'unpaid ecmt permits input' => [
                'input' => [
                    'results' => [
                        [
                            'permitNumber' => 111,
                            'irhpPermitRange' => [
                                'emissionsCategory' => [
                                    'id' => 'EURO5'
                                ],
                                'countrys' => [
                                    [
                                        'id' => 'AA',
                                    ],
                                    [
                                        'id' => 'BB',
                                    ],
                                    [
                                        'id' => 'CC',
                                    ],
                                ],
                                'irhpPermitStock' => [
                                    'validTo' => '2019-10-11 16:40:17',
                                ],
                            ],
                            'irhpPermitApplication' => [
                                'id' => 100
                            ],
                            'startDate' => '2018-10-11 16:40:17',
                        ],
                        [
                            'permitNumber' => 222,
                            'irhpPermitRange' => [
                                'emissionsCategory' => [
                                    'id' => 'EURO5'
                                ],
                                'countrys' => [],
                                'irhpPermitStock' => [
                                    'validTo' => '2019-10-11 16:40:17',
                                ],
                            ],
                            'irhpPermitApplication' => [
                                'id' => 100
                            ],
                            'startDate' => '2018-10-11 16:40:17',
                        ],
                    ],
                    'count' => 2,
                ],
                'expected' => [
                    'results' => [
                        [
                            'permitNumber' => 111,
                            'emissionsCategory' => [
                                'id' => 'EURO5'
                            ],
                            'countries' => [
                                [
                                    'id' => 'AA',
                                ],
                                [
                                    'id' => 'BB',
                                ],
                                [
                                    'id' => 'CC',
                                ],
                            ],
                            'irhpPermitApplication' => [
                                'id' => 100
                            ],
                            'startDate' => '2018-10-11 16:40:17',
                            'expiryDate' => '2019-10-11 16:40:17',
                        ],
                        [
                            'permitNumber' => 222,
                            'emissionsCategory' => [
                                'id' => 'EURO5'
                            ],
                            'countries' => [],
                            'irhpPermitApplication' => [
                                'id' => 100
                            ],
                            'startDate' => '2018-10-11 16:40:17',
                            'expiryDate' => '2019-10-11 16:40:17',
                        ],
                    ],
                    'count' => 2,
                ],
            ],
        ];
    }
}
