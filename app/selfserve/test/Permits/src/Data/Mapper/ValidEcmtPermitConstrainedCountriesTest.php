<?php

namespace PermitsTest\Data\Mapper;

use Permits\Controller\Config\DataSource\EcmtConstrainedCountriesList as EcmtConstrainedCountriesListDataSource;
use Permits\Controller\Config\DataSource\ValidEcmtPermits as ValidEcmtPermitsDataSource;
use Permits\Controller\Config\DataSource\UnpaidEcmtPermits as UnpaidEcmtPermitsDataSource;
use Permits\Data\Mapper\ValidEcmtPermitConstrainedCountries;

/**
 * ValidEcmtPermitConstrainedCountriesTest
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ValidEcmtPermitConstrainedCountriesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider dpTestMapForDisplay
     */
    public function testMapForDisplay($input, $expected)
    {
        self::assertEquals($expected, ValidEcmtPermitConstrainedCountries::mapForDisplay($input));
    }

    public function dpTestMapForDisplay()
    {
        return [
            'empty input' => [
                'input' => [],
                'expected' => [],
            ],
            'unpaid ecmt permits input' => [
                'input' => [
                    EcmtConstrainedCountriesListDataSource::DATA_KEY => [
                        'results' => [
                            [
                                'id' => 'AA',
                            ],
                            [
                                'id' => 'BB',
                            ],
                            [
                                'id' => 'CC',
                            ],
                        ]
                    ],
                    UnpaidEcmtPermitsDataSource::DATA_KEY => [
                        'results' => [
                            [
                                'permitNumber' => 111,
                                'emissionsCategory' => 'Euro 5',
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
                                'permitNumber' => 222,
                                'emissionsCategory' => 'Euro 5',
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
                                'permitNumber' => 333,
                                'emissionsCategory' => 'Euro 5',
                                'countries' => [
                                    [
                                        'id' => 'AA',
                                    ],
                                ],
                            ],
                            [
                                'permitNumber' => 444,
                                'emissionsCategory' => 'Euro 5',
                                'countries' => [],
                            ],
                        ],
                    ],
                ],
                'expected' => [
                    'results' => [
                        [
                            'permitNumber' => 111,
                            'emissionsCategory' => 'Euro 5',
                            'countries' => [], //no exceptions
                            'irhpPermitApplication' => null,
                            'startDate' => null,
                            'expiryDate' => null,
                        ],
                        [
                            'permitNumber' => 222,
                            'emissionsCategory' => 'Euro 5',
                            'countries' => [
                                [
                                    'id' => 'CC',
                                ],
                            ], //countries "AA" and "BB" were matched
                            'irhpPermitApplication' => null,
                            'startDate' => null,
                            'expiryDate' => null,
                        ],
                        [
                            'permitNumber' => 333,
                            'emissionsCategory' => 'Euro 5',
                            'countries' => [
                                [
                                    'id' => 'BB',
                                ],
                                [
                                    'id' => 'CC',
                                ], //country "AA" was matched
                            ],
                            'irhpPermitApplication' => null,
                            'startDate' => null,
                            'expiryDate' => null,
                        ],
                        [
                            'permitNumber' => 444,
                            'emissionsCategory' => 'Euro 5',
                            'countries' => [
                                [
                                    'id' => 'AA',
                                ],
                                [
                                    'id' => 'BB',
                                ],
                                [
                                    'id' => 'CC',
                                ], //all countries matched
                            ],
                            'irhpPermitApplication' => null,
                            'startDate' => null,
                            'expiryDate' => null,
                        ],
                    ],
                ],
            ],
            'valid ecmt permits input' => [
                'input' => [
                    EcmtConstrainedCountriesListDataSource::DATA_KEY => [
                        'results' => [
                            [
                                'id' => 'AA',
                            ],
                            [
                                'id' => 'BB',
                            ],
                            [
                                'id' => 'CC',
                            ],
                        ]
                    ],
                    ValidEcmtPermitsDataSource::DATA_KEY => [
                        'results' => [
                            [
                                'permitNumber' => 111,
                                'emissionsCategory' => 'Euro 5',
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
                                'irhpPermitApplication' => ['id' => 500],
                                'startDate' => '2018-10-11 16:40:17',
                                'expiryDate' => '2019-10-11 16:40:17',
                            ],
                            [
                                'permitNumber' => 222,
                                'emissionsCategory' => 'Euro 5',
                                'countries' => [
                                    [
                                        'id' => 'AA',
                                    ],
                                    [
                                        'id' => 'BB',
                                    ],
                                ],
                                'irhpPermitApplication' => ['id' => 500],
                                'startDate' => '2018-10-11 16:40:17',
                                'expiryDate' => '2019-10-11 16:40:17',
                            ],
                            [
                                'permitNumber' => 333,
                                'emissionsCategory' => 'Euro 5',
                                'countries' => [
                                    [
                                        'id' => 'AA',
                                    ],
                                ],
                                'irhpPermitApplication' => ['id' => 500],
                                'startDate' => '2018-10-11 16:40:17',
                                'expiryDate' => '2019-10-11 16:40:17',
                            ],
                            [
                                'permitNumber' => 444,
                                'emissionsCategory' => 'Euro 5',
                                'countries' => [],
                                'irhpPermitApplication' => ['id' => 500],
                                'startDate' => '2018-10-11 16:40:17',
                                'expiryDate' => '2019-10-11 16:40:17',
                            ],
                        ],
                    ],
                ],
                'expected' => [
                    'results' => [
                        [
                            'permitNumber' => 111,
                            'emissionsCategory' => 'Euro 5',
                            'countries' => [], //no exceptions
                            'irhpPermitApplication' => ['id' => 500],
                            'startDate' => '2018-10-11 16:40:17',
                            'expiryDate' => '2019-10-11 16:40:17',
                        ],
                        [
                            'permitNumber' => 222,
                            'emissionsCategory' => 'Euro 5',
                            'countries' => [
                                [
                                    'id' => 'CC',
                                ],
                            ], //countries "AA" and "BB" were matched
                            'irhpPermitApplication' => ['id' => 500],
                            'startDate' => '2018-10-11 16:40:17',
                            'expiryDate' => '2019-10-11 16:40:17',
                        ],
                        [
                            'permitNumber' => 333,
                            'emissionsCategory' => 'Euro 5',
                            'countries' => [
                                [
                                    'id' => 'BB',
                                ],
                                [
                                    'id' => 'CC',
                                ], //country "AA" was matched
                            ],
                            'irhpPermitApplication' => ['id' => 500],
                            'startDate' => '2018-10-11 16:40:17',
                            'expiryDate' => '2019-10-11 16:40:17',
                        ],
                        [
                            'permitNumber' => 444,
                            'emissionsCategory' => 'Euro 5',
                            'countries' => [
                                [
                                    'id' => 'AA',
                                ],
                                [
                                    'id' => 'BB',
                                ],
                                [
                                    'id' => 'CC',
                                ], //all countries matched
                            ],
                            'irhpPermitApplication' => ['id' => 500],
                            'startDate' => '2018-10-11 16:40:17',
                            'expiryDate' => '2019-10-11 16:40:17',
                        ],
                    ],
                ],
            ],
        ];
    }
}
