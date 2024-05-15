<?php

namespace OlcsTest\Data\Mapper;

use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * IrhpPermitTest
 */
class IrhpPermitTest extends MockeryTestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new \Olcs\Data\Mapper\IrhpPermit();
    }

    /**
     * @dataProvider mapFromResultDataProvider
     *
     * @param $data
     * @param $expected
     */
    public function testMapPermitData($data, $expected)
    {
        $this->assertSame($expected, $this->sut->mapFromResult($data));
    }

    public function mapFromResultDataProvider()
    {
        return [
            [
                [
                    'irhpPermitRange' => [

                        'countrys' => [],
                        'irhpPermitStock' => [ 'country' => ['countryDesc' => 'Germany'] ],
                        'permitNumber' => 499,
                        'status' => [
                            'description' => 'Awaiting printing',
                            'id' => 'irhp_permit_awaiting_printing'
                        ],
                        'startDate' => [
                            'date' => '2019-04-01 00:00:00.000000'
                        ]

                    ]
                ],
                [
                    'irhpPermitRange' => [

                        'countrys' => [],
                        'irhpPermitStock' => [ 'country' => ['countryDesc' => 'Germany'] ],
                        'permitNumber' => 499,
                        'status' => [
                            'description' => 'Awaiting printing',
                            'id' => 'irhp_permit_awaiting_printing'
                        ],
                        'startDate' => [
                            'date' => '2019-04-01 00:00:00.000000'
                        ]
                    ],
                    'country' => '<div class="article"><ul><li>Germany</li></ul></div>'
                ]
            ],
            [
                [
                    'irhpPermitRange' => [
                        'countrys' => [
                            [
                                'countryDesc' => 'Austria',
                                'id' => 'AT'
                            ],
                            [
                                'countryDesc' => 'Russia',
                                'id' => 'RU'
                            ]
                        ],
                        'irhpPermitStock' => [ 'country' => [] ],
                        'permitNumber' => 499,
                        'status' => [
                            'description' => 'Awaiting printing',
                            'id' => 'irhp_permit_awaiting_printing'
                        ],
                        'startDate' => [
                            'date' => '2019-04-01 00:00:00.000000'
                        ]

                    ]
                ],
                [
                    'irhpPermitRange' => [
                        'countrys' => [
                            [
                                'countryDesc' => 'Austria',
                                'id' => 'AT'
                            ],
                            [
                                'countryDesc' => 'Russia',
                                'id' => 'RU'
                            ]
                        ],
                        'irhpPermitStock' => [ 'country' => [] ],
                        'permitNumber' => 499,
                        'status' => [
                            'description' => 'Awaiting printing',
                            'id' => 'irhp_permit_awaiting_printing'
                        ],
                        'startDate' => [
                            'date' => '2019-04-01 00:00:00.000000'
                        ]
                    ],
                    'restrictedCountries' => '<div class="article"><ul><li>Austria</li><li>Russia</li></ul></div>'
                ]
            ]
        ];
    }
}
