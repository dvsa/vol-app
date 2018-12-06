<?php

namespace PermitsTest\Data\Mapper;

use Permits\Data\Mapper\ValidEcmtPermitConstrainedCountries;

/**
 * ValidEcmtPermitConstrainedCountriesTest
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ValidEcmtPermitConstrainedCountriesTest extends \PHPUnit_Framework_TestCase
{
    public function testMapForDisplayEmptyData()
    {
        self::assertEquals([], ValidEcmtPermitConstrainedCountries::mapForDisplay([]));
    }

    public function testMapForDisplay()
    {
        $inputData = [
            'ecmtConstrainedCountries' => [
                'results' => [
                    0 => [
                        'id' => 'AA',
                    ],
                    1 => [
                        'id' => 'BB',
                    ],
                    2 => [
                        'id' => 'CC',
                    ],
                ]
            ],
            'validPermits' => [
                'results' => [
                    0 => [
                        'permitNumber' => 111,
                        'status' => ['id' => 'permit_app_valid', 'description' => 'Valid' ],
                        'issueDate' => '2018-10-11 16:40:17',
                        'countries' => [
                            0 => [
                                'id' => 'AA',
                            ],
                            1 => [
                                'id' => 'BB',
                            ],
                            2 => [
                                'id' => 'CC',
                            ],

                        ],
                    ],
                    1 => [
                        'permitNumber' => 222,
                        'status' => ['id' => 'permit_app_valid', 'description' => 'Valid' ],
                        'issueDate' => '2018-10-11 16:40:17',
                        'countries' => [
                            0 => [
                                'id' => 'AA',
                            ],
                            1 => [
                                'id' => 'BB',
                            ],
                        ],
                    ],
                    2 => [
                        'permitNumber' => 333,
                        'status' => ['id' => 'permit_app_valid', 'description' => 'Valid' ],
                        'issueDate' => '2018-10-11 16:40:17',
                        'countries' => [
                            0 => [
                                'id' => 'AA',
                            ],
                        ],
                    ],
                    3 => [
                        'permitNumber' => 444,
                        'status' => ['id' => 'permit_app_valid', 'description' => 'Valid' ],
                        'issueDate' => '2018-10-11 16:40:17',
                        'countries' => [],
                    ],
                ],
            ],
        ];

        $outputData = [
            'results' => [
                0 => [
                    'permitNumber' => 111,
                    'status' => ['id' => 'permit_app_valid', 'description' => 'Valid' ],
                    'issueDate' => '2018-10-11 16:40:17',
                    'countries' => [], //no exceptions
                ],
                1 => [
                    'permitNumber' => 222,
                    'status' => ['id' => 'permit_app_valid', 'description' => 'Valid' ],
                    'issueDate' => '2018-10-11 16:40:17',
                    'countries' => [
                        0 => [
                            'id' => 'CC',
                        ],
                    ], //countries "AA" and "BB" were matched
                ],
                2 => [
                    'permitNumber' => 333,
                    'status' => ['id' => 'permit_app_valid', 'description' => 'Valid' ],
                    'issueDate' => '2018-10-11 16:40:17',
                    'countries' => [
                        0 => [
                            'id' => 'BB',
                        ],
                        1 => [
                            'id' => 'CC',
                        ], //country "AA" was matched
                    ],
                ],
                3 => [
                    'permitNumber' => 444,
                    'status' => ['id' => 'permit_app_valid', 'description' => 'Valid' ],
                    'issueDate' => '2018-10-11 16:40:17',
                    'countries' => [
                        0 => [
                            'id' => 'AA',
                        ],
                        1 => [
                            'id' => 'BB',
                        ],
                        2 => [
                            'id' => 'CC',
                        ], //all countries matched
                    ],
                ],
            ],
        ];

        self::assertEquals($outputData, ValidEcmtPermitConstrainedCountries::mapForDisplay($inputData));
    }
}
