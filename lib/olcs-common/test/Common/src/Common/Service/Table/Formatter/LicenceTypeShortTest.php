<?php

/**
 * LicenceTypeShort formatter test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Service\Table\Formatter;

use Common\RefData;
use Common\Service\Table\Formatter\LicenceTypeShort;

/**
 * LicenceTypeShort formatter test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceTypeShortTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provider
     */
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, (new LicenceTypeShort())->format($data));
    }

    /**
     * @return (((string|string[])[]|string)[]|string)[][]
     *
     * @psalm-return array{'nothing set': list{list{'licence'}, ''}, gv: list{array{licence: array{goodsOrPsv: array{id: 'lcat_gv'}}}, 'GV'}, psv: list{array{licence: array{goodsOrPsv: array{id: 'lcat_psv'}}}, 'PSV'}, restricted: list{array{licence: array{licenceType: array{id: 'ltyp_r'}}}, 'R'}, 'special restricted': list{array{licence: array{licenceType: array{id: 'ltyp_sr'}}}, 'SR'}, 'standard national': list{array{licence: array{licenceType: array{id: 'ltyp_sn'}}}, 'SN'}, 'standard international': list{array{licence: array{licenceType: array{id: 'ltyp_si'}}}, 'SI'}, 'combined: gv sn': list{array{licence: array{goodsOrPsv: array{id: 'lcat_gv'}, licenceType: array{id: 'ltyp_si'}}}, 'GV-SI'}, 'combined: psv sr': list{array{licence: array{goodsOrPsv: array{id: 'lcat_psv'}, licenceType: array{id: 'ltyp_sr'}}}, 'PSV-SR'}, 'combined: psv sr ON licence': list{array{goodsOrPsv: array{id: 'lcat_psv'}, licenceType: array{id: 'ltyp_sr'}}, 'PSV-SR'}}
     */
    public function provider(): array
    {
        return [
            'nothing set' => [
                [
                    'licence'
                ],
                ''
            ],
            'gv' => [
                [
                    'licence' => [
                        'goodsOrPsv' => [
                            'id' => RefData::LICENCE_CATEGORY_GOODS_VEHICLE
                        ]
                    ]
                ],
                'GV'
            ],
            'psv' => [
                [
                    'licence' => [
                        'goodsOrPsv' => [
                            'id' => RefData::LICENCE_CATEGORY_PSV
                        ]
                    ]
                ],
                'PSV'
            ],
            'restricted' => [
                [
                    'licence' => [
                        'licenceType' => [
                            'id' => RefData::LICENCE_TYPE_RESTRICTED
                        ]
                    ]
                ],
                'R'
            ],
            'special restricted' => [
                [
                    'licence' => [
                        'licenceType' => [
                            'id' => RefData::LICENCE_TYPE_SPECIAL_RESTRICTED
                        ]
                    ]
                ],
                'SR'
            ],
            'standard national' => [
                [
                    'licence' => [
                        'licenceType' => [
                            'id' => RefData::LICENCE_TYPE_STANDARD_NATIONAL
                        ]
                    ]
                ],
                'SN'
            ],
            'standard international' => [
                [
                    'licence' => [
                        'licenceType' => [
                            'id' => RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL
                        ]
                    ]
                ],
                'SI'
            ],
            'combined: gv sn' => [
                [
                    'licence' => [
                        'goodsOrPsv' => [
                            'id' => RefData::LICENCE_CATEGORY_GOODS_VEHICLE
                        ],
                        'licenceType' => [
                            'id' => RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL
                        ]
                    ]
                ],
                'GV-SI'
            ],
            'combined: psv sr' => [
                [
                    'licence' => [
                        'goodsOrPsv' => [
                            'id' => RefData::LICENCE_CATEGORY_PSV
                        ],
                        'licenceType' => [
                            'id' => RefData::LICENCE_TYPE_SPECIAL_RESTRICTED
                        ]
                    ]
                ],
                'PSV-SR'
            ],
            'combined: psv sr ON licence' => [
                [
                    'goodsOrPsv' => [
                        'id' => RefData::LICENCE_CATEGORY_PSV
                    ],
                    'licenceType' => [
                        'id' => RefData::LICENCE_TYPE_SPECIAL_RESTRICTED
                    ]
                ],
                'PSV-SR'
            ]
        ];
    }
}
