<?php

namespace OlcsTest\Data\Mapper;

use Common\RefData;
use Common\Service\Qa\DataTransformer\ApplicationStepsPostDataTransformer;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * IrhpApplicationTest
 */
class IrhpApplicationTest extends MockeryTestCase
{
    private $applicationStepsPostDataTransformer;

    public function setUp(): void
    {
        $this->applicationStepsPostDataTransformer = m::mock(ApplicationStepsPostDataTransformer::class);

        $this->sut = new \Olcs\Data\Mapper\IrhpApplication($this->applicationStepsPostDataTransformer);
    }

    public function testMapFromResult()
    {
        $data = [
            'id' => '123',
            'dateReceived' => '2019-01-30',
            'status' => [
                'id' => 'permit_status',
            ],
            'declaration' => '1',
            'checked' => '1',
            'corCertificateNumber' => 'ABC123',
            'irhpPermitType' => [
                'id' => 1,
                'isApplicationPathEnabled' => true,
            ],
            'requiresPreAllocationCheck' => true,
            'isApplicationPathEnabled' => true,
        ];

        $expectedFormData = [
            'fields' => [
                'id' => '123',
                'dateReceived' => '2019-01-30',
                'status' => 'permit_status',
                'declaration' => '1',
                'checked' => '1',
                'corCertificateNumber' => 'ABC123',
                'irhpPermitType' => 1,
                'requiresPreAllocationCheck' => true,
                'isApplicationPathEnabled' => true,
            ],
            'topFields' => [
                'id' => '123',
                'dateReceived' => '2019-01-30',
                'status' => 'permit_status',
                'requiresPreAllocationCheck' => true,
                'isApplicationPathEnabled' => true,
                'irhpPermitType' => 1,
            ],
            'bottomFields' => [
                'declaration' => '1',
                'checked' => '1',
                'corCertificateNumber' => 'ABC123',
            ],
        ];

        $this->assertSame(
            $expectedFormData,
            $this->sut->mapFromResult($data)
        );
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

    public function testMapFromFormShortTerm()
    {
        $preTransformedQaData = ['foo' => 'bar'];
        $postTransformedQaData = ['transformedFoo' => 'transformedBar'];

        $applicationSteps = [
            [
                'fieldsetName' => 'fieldset72',
                'slug' => 'no-of-permits'
            ],
            [
                'fieldsetName' => 'fieldset43',
                'slug' => 'restricted-countries'
            ]
        ];

        $this->applicationStepsPostDataTransformer->shouldReceive('getTransformed')
            ->with($applicationSteps, $preTransformedQaData)
            ->andReturn($postTransformedQaData);

        $formData =
            [
                'topFields' => [
                    'isApplicationPathEnabled' => true,
                    'id' => 1,
                    'licence' => 7,
                    'irhpPermitType' => 2,
                    'dateReceived' => '2090-01-01'
                ],
                'qa' => $preTransformedQaData,
                'bottomFields' => [
                    'corCertificateNumber' => 'ABC123',
                    'declaration' => 1
                ]
            ];

        $expected = [
            'id' => 1,
            'dateReceived' => '2090-01-01',
            'declaration' => 1,
            'postData' => ['qa' => $postTransformedQaData],
            'corCertificateNumber' => 'ABC123',
        ];

        $this->assertSame($expected, $this->sut->mapFromForm($formData, $applicationSteps));
    }

    public function testMapFromFormBilateral()
    {
        $topFields = [
            'irhpPermitType' => RefData::IRHP_BILATERAL_PERMIT_TYPE_ID,
            'isApplicationPathEnabled' => false
        ];

        $bottomFields = [
            'key1' => 'value1',
            'key2' => 'value2'
        ];

        $fields = [
            'selectedCountriesCsv' => 'FR,NO',
            'countries' => [
                'FR' => [
                    'selectedPeriodId' => 11,
                    'periods' => [
                        'period11' => [
                            'standard-journey_single' => '',
                            'cabotage-journey_single' => '8'
                        ],
                        'period12' => [
                            'standard-journey_multiple' => '4',
                            'cabotage-journey_multiple' => '11'
                        ]
                    ]
                ],
                'NO' => [
                    'selectedPeriodId' => 25,
                    'periods' => [
                        'period23' => [
                            'standard-journey_multiple' => '5',
                            'cabotage-journey_multiple' => '15'
                        ],
                        'period25' => [
                            'standard-journey_single' => '18',
                            'cabotage-journey_single' => '0',
                            'standard-journey_multiple' => '14',
                            'cabotage-journey_single' => '12'
                        ]
                    ]
                ],
                'CH' => [
                    'selectedPeriodId' => 20,
                    'periods' => [
                        'period20' => [
                            'cabotage-journey_single' => '2'
                        ],
                        'period12' => [
                            'standard-journey_multiple' => '10',
                        ]
                    ]
                ]
            ]
        ];

        $data = [
            'topFields' => $topFields,
            'bottomFields' => $bottomFields,
            'fields' => $fields
        ];

        $expected = array_merge(
            $topFields,
            $bottomFields,
            $fields,
            [
                'permitsRequired' => [
                    'FR' => [
                        'periodId' => 11,
                        'permitsRequired' => [
                            'cabotage-journey_single' => '8'
                        ]
                    ],
                    'NO' => [
                        'periodId' => 25,
                        'permitsRequired' => [
                            'standard-journey_single' => '18',
                            'standard-journey_multiple' => '14',
                            'cabotage-journey_single' => '12'
                        ]
                    ]
                ]
            ]
        );

        $this->assertEquals(
            $expected,
            $this->sut->mapFromForm($data)
        );
    }
}
