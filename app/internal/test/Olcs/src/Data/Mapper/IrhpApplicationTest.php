<?php

namespace OlcsTest\Data\Mapper;

use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * IrhpApplicationTest
 */
class IrhpApplicationTest extends MockeryTestCase
{
    public function setup()
    {
        $this->sut = new \Olcs\Data\Mapper\IrhpApplication();
    }

    public function testMapApplicationData()
    {
        $formData =
            [
                'fields' => [
                    'id' => '1',
                    'licence' => [
                        'id' => '7',
                        'licNo' => 'OB1234567',
                        'goodsOrPsv' => 'lcat_gv',
                        'licenceType' => 'ltyp_si',
                        'status' => 'lsts_valid',
                        'totAuthTrailers' => '4',
                        'totAuthVehicles' => '12',
                        'expiryDate' => '2020-01-01',
                        'grantedDate' => null,


                    ],
                    'checkedAnswers' => '0',
                    'declaration' => '0',
                    'source' => 'app_source_selfserve',
                    'status' => 'permit_app_nys',
                    'irhpPermitType' => [
                        'id' => '4',
                        'name' => 'permit_annual_bilateral',
                        'description' => 'This bilateral permit allows road haulage to certain EU and EEA countries that have agreements with the UK. You’ll need a separate permit for each country you intend to travel to and also for each vehicle you intend to use.',
                    ],
                    'dateReceived' => '2019-01-30',
                    'irhpPermitApplications' => [
                        [
                            'id' => '45',
                            'permitsRequired' => '2',
                            'irhpPermitWindow' => [
                                'id' => '14',
                                'irhpPermitStock' => [
                                    'id' => '14',
                                    'irhpPermitType' => [
                                        'id' => '4',
                                        'name' => 'permit_annual_bilateral',
                                        'description' => 'This bilateral permit allows road haulage to certain EU and EEA countries that have agreements with the UK. You’ll need a separate permit for each country you intend to travel to and also for each vehicle you intend to use.',
                                    ],
                                    'country' => [
                                        'id' => 'NL',
                                        'countryDesc' => 'Netherlands',
                                    ],
                                    'validFrom' => '2020-01-01',
                                    'validTo' => '2020-12-31',
                                ],
                                'startDate' => '2019-01-01 00:00:00',
                                'endDate' => '2019-09-29 00:00:00',
                                'daysForPayment' => '14',
                            ]
                        ],
                        [
                            'id' => '46',
                            'irhpPermitWindow' => [
                                'id' => '13',
                                'irhpPermitStock' => [
                                    'id' => '13',
                                    'irhpPermitType' => [
                                        'id' => '4',
                                        'name' => 'permit_annual_bilateral',
                                        'description' => 'This bilateral permit allows road haulage to certain EU and EEA countries that have agreements with the UK. You’ll need a separate permit for each country you intend to travel to and also for each vehicle you intend to use.',
                                    ],
                                    'country' => [
                                        'id' => 'PL',
                                        'countryDesc' => 'Poland',
                                        'isMemberState' => '1',
                                        'isEcmtState' => '1',
                                        'isEeaState' => '1',
                                    ],
                                    'validFrom' => '2020-01-01',
                                    'validTo' => '2020-12-31',
                                ],
                                'startDate' => '2019-01-01 00:00:00',
                                'endDate' => '2019-09-29 00:00:00',

                            ],
                            'permitsRequired' => '2',
                        ]
                    ]
                ]
            ];

        $windowData =
            [
                [
                    'id' => '14',
                    'irhpPermitStock' => [
                        'id' => '14',
                        'irhpPermitType' => [
                            'id' => '4',
                            'name' => 'permit_annual_bilateral',
                            'description' => 'This bilateral permit allows road haulage to certain EU and EEA countries that have agreements with the UK. You’ll need a separate permit for each country you intend to travel to and also for each vehicle you intend to use.',
                        ],
                        'country' => [
                            'id' => 'NL',
                            'countryDesc' => 'Netherlands',
                        ],
                        'validFrom' => '2020-01-01',
                        'validTo' => '2020-12-31',
                    ],
                    'startDate' => '2019-01-01 00:00:00',
                    'endDate' => '2019-09-29 00:00:00',
                    'daysForPayment' => '14',
                ],
                [
                    'id' => '13',
                    'irhpPermitStock' => [
                        'id' => '13',
                        'irhpPermitType' => [
                            'id' => '4',
                            'name' => 'permit_annual_bilateral',
                            'description' => 'This bilateral permit allows road haulage to certain EU and EEA countries that have agreements with the UK. You’ll need a separate permit for each country you intend to travel to and also for each vehicle you intend to use.',
                        ],
                        'country' => [
                            'id' => 'PL',
                            'countryDesc' => 'Poland',
                        ],
                        'validFrom' => '2020-01-01',
                        'validTo' => '2020-12-31',
                    ],
                    'startDate' => '2019-01-01 00:00:00',
                    'endDate' => '2019-09-29 00:00:00',
                ]
            ];

        $expected = [
            'irhpPermitType' => [
                'id' => 4
            ],
            'irhpPermitApplications' => [
                [
                    'permitsRequired' => '2',
                    'irhpPermitWindow' => [
                        'irhpPermitStock' => [
                            'id' => '14',
                            'irhpPermitType' => [
                                'id' => '4',
                                'name' => 'permit_annual_bilateral',
                                'description' => 'This bilateral permit allows road haulage to certain EU and EEA countries that have agreements with the UK. You’ll need a separate permit for each country you intend to travel to and also for each vehicle you intend to use.'
                            ],
                            'country' => [
                                'id' => 'NL',
                                'countryDesc' => 'Netherlands'
                            ],
                            'validFrom' => '2020-01-01',
                            'validTo' => '2020-12-31'
                        ]
                    ]
                ],
                [
                    'permitsRequired' => '2',
                    'irhpPermitWindow' => [
                        'irhpPermitStock' => [
                            'id' => '13',
                            'irhpPermitType' => [
                                'id' => '4',
                                'name' => 'permit_annual_bilateral',
                                'description' => 'This bilateral permit allows road haulage to certain EU and EEA countries that have agreements with the UK. You’ll need a separate permit for each country you intend to travel to and also for each vehicle you intend to use.'
                            ],
                            'country' => [
                                'id' => 'PL',
                                'countryDesc' => 'Poland'
                            ],
                            'validFrom' => '2020-01-01',
                            'validTo' => '2020-12-31'
                        ]
                    ]
                ]
            ]
        ];

        $this->assertSame($expected, $this->sut->mapApplicationData($windowData, 4, $formData));
    }
}
