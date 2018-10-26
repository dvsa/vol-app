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
                        'countries' => [],
                    ],
                    1 => [
                        'permitNumber' => 222,
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
                            3 => [
                                'id' => 'DD',
                            ],
                        ],
                    ],
                    2 => [
                        'permitNumber' => 333,
                        'countries' => [
                            0 => [
                                'id' => 'AA',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $outputData = [
            'results' => [
                0 => [
                    'permitNumber' => 111,
                    'countries' => [], //there were no countries
                ],
                1 => [
                    'permitNumber' => 222,
                    'countries' => [], //all constrained countries covered, extra country "DD" ignored
                ],
                2 => [
                    'permitNumber' => 333,
                    'countries' => [
                        1 => [
                            'id' => 'BB',
                        ],
                        2 => [
                            'id' => 'CC',
                        ], //country "AA" was matched
                    ],
                ],
            ],
        ];

        self::assertEquals($outputData, ValidEcmtPermitConstrainedCountries::mapForDisplay($inputData));
    }
}
