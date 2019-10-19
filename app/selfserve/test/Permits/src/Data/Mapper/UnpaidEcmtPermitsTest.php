<?php

namespace PermitsTest\Data\Mapper;

use Permits\Data\Mapper\UnpaidEcmtPermits;

/**
 * UnpaidEcmtPermitsTest
 */
class UnpaidEcmtPermitsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider dpTestMapForDisplay
     */
    public function testMapForDisplay($input, $expected)
    {
        $unpaidEcmtPermits = new UnpaidEcmtPermits();

        $this->assertEquals(
            $expected,
            $unpaidEcmtPermits->mapForDisplay($input)
        );
    }

    public function dpTestMapForDisplay()
    {
        return [
            'unpaid ecmt permits input' => [
                'input' => [
                    'result' => [
                        [
                            'permitNumber' => 1,
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
                            ],
                        ],
                        [
                            'permitNumber' => 2,
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
                                ],
                            ],
                        ],
                        [
                            'permitNumber' => 3,
                            'irhpPermitRange' => [
                                'emissionsCategory' => [
                                    'id' => 'EURO5'
                                ],
                                'countrys' => [
                                    [
                                        'id' => 'AA',
                                    ],
                                ],
                            ],
                        ],
                        [
                            'permitNumber' => 4,
                            'irhpPermitRange' => [
                                'emissionsCategory' => [
                                    'id' => 'EURO5'
                                ],
                                'countrys' => [
                                ],
                            ],
                        ],
                    ],
                    'count' => 4,
                ],
                'expected' => [
                    'results' => [
                        [
                            'permitNumber' => 1,
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
                        ],
                        [
                            'permitNumber' => 2,
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
                            ],
                        ],
                        [
                            'permitNumber' => 3,
                            'emissionsCategory' => [
                                'id' => 'EURO5'
                            ],
                            'countries' => [
                                [
                                    'id' => 'AA',
                                ],
                            ],
                        ],
                        [
                            'permitNumber' => 4,
                            'emissionsCategory' => [
                                'id' => 'EURO5'
                            ],
                            'countries' => [
                            ],
                        ],
                    ],
                    'count' => 4,
                ],
            ],
        ];
    }
}
