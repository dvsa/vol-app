<?php

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\EnvironmentalComplaint as Sut;
use Laminas\Form\FormInterface;

/**
 * EnvironmentalComplaint Mapper Test
 */
class EnvironmentalComplaintTest extends MockeryTestCase
{
    /**
     * @dataProvider mapFromResultDataProvider
     *
     * @param $inData
     * @param $expected
     */
    public function testMapFromResult($inData, $expected)
    {
        $this->assertEquals($expected, Sut::mapFromResult($inData));
    }

    public function mapFromResultDataProvider()
    {
        return [
            // add
            [
                [],
                ['fields' => []]
            ],
            // edit
            [
                [
                    'id' => 99,
                    'version' => 3,
                    'case' => [
                        'id' => 24
                    ],
                    'complainantContactDetails' => [
                        'person' => [
                            'forename' => 'Joe',
                            'familyName' => 'Smith'
                        ],
                        'address' => [
                            'addressLine1' => 'a1',
                            'town' => 'town',
                            'postcode' => 'ls9 1aa',
                            'countryCode' => 'GB',
                        ]
                    ],
                    'operatingCentres' => [
                        ['id' => 101],
                        ['id' => 102],
                    ]
                ],
                [
                    'fields' => [
                        'id' => 99,
                        'version' => 3,
                        'case' => 24,
                        'complainantForename' => 'Joe',
                        'complainantFamilyName' => 'Smith',
                        'complainantContactDetails' => [
                            'person' => [
                                'forename' => 'Joe',
                                'familyName' => 'Smith'
                            ],
                            'address' => [
                                'addressLine1' => 'a1',
                                'town' => 'town',
                                'postcode' => 'ls9 1aa',
                                'countryCode' => 'GB',
                            ]
                        ],
                        'operatingCentres' => [101, 102]
                    ],
                    'address' => [
                        'addressLine1' => 'a1',
                        'town' => 'town',
                        'postcode' => 'ls9 1aa',
                        'countryCode' => 'GB',
                    ]
                ]
            ],
        ];
    }

    public function testMapFromForm()
    {
        $inData = [
            'fields' => [
                'id' => 99,
                'version' => 3,
                'complainantForename' => 'Joe',
                'complainantFamilyName' => 'Smith',
                'operatingCentres' => [101, 102]
            ],
            'address' => [
                'addressLine1' => 'a1',
                'town' => 'town',
                'postcode' => 'ls9 1aa',
                'countryCode' => 'GB',
            ]
        ];

        $expected = [
            'id' => 99,
            'version' => 3,
            'complainantForename' => 'Joe',
            'complainantFamilyName' => 'Smith',
            'complainantContactDetails' => [
                'person' => [
                    'forename' => 'Joe',
                    'familyName' => 'Smith'
                ],
                'address' => [
                    'addressLine1' => 'a1',
                    'town' => 'town',
                    'postcode' => 'ls9 1aa',
                    'countryCode' => 'GB',
                ]
            ],
            'operatingCentres' => [101, 102]
        ];

        $this->assertEquals($expected, Sut::mapFromForm($inData));
    }

    public function testMapFromErrors()
    {
        $mockForm = m::mock(FormInterface::class);
        $errors = ['field' => 'data'];

        $this->assertEquals($errors, Sut::mapFromErrors($mockForm, $errors));
    }
}
