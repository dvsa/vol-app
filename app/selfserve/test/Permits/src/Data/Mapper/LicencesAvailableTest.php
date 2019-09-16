<?php

namespace PermitsTest\Data\Mapper;

use Common\Form\Elements\Types\HtmlTranslated;
use Common\Form\Form;
use Common\RefData;
use Mockery as m;
use Permits\Data\Mapper\LicencesAvailable;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

class LicencesAvailableTest extends TestCase
{
    private $licencesAvailable;

    public function setUp()
    {
        $this->licencesAvailable = new LicencesAvailable();
    }

    public function testMapForFormOptionsOneEcmtRestricted()
    {
        $inputData = [
            'irhpPermitType' => [
                'id' => RefData::ECMT_PERMIT_TYPE_ID,
            ],
            'licencesAvailable' => [
                'eligibleLicences' => [
                    'result' => [
                        0 => [
                            'id' => 7,
                            'licNo' => 'OB1234567',
                            'trafficArea' => 'North East of England',
                            'licenceType' => [
                                'description' => 'Restricted',
                                'id' => 'ltyp_r',
                            ],
                            'canMakeEcmtApplication' => true,
                        ],
                    ]
                ]
            ]
        ];

        $outputData = [
            'irhpPermitType' => [
                'id' => RefData::ECMT_PERMIT_TYPE_ID,
            ],
            'licencesAvailable' => [
                'eligibleLicences' => [
                    'result' => [
                        0 => [
                            'id' => 7,
                            'licNo' => 'OB1234567',
                            'trafficArea' => 'North East of England',
                            'licenceType' => [
                                'description' => 'Restricted',
                                'id' => 'ltyp_r',
                            ],
                            'canMakeEcmtApplication' => true,
                        ],
                    ]
                ]
            ],
            'question' => LicencesAvailable::ECMT_QUESTION_ONE_LICENCE,
            'questionArgs' => ['OB1234567 Restricted (North East of England)'],
        ];

        $valueOptions = [
            7 => [
                'value' => 7,
                'label' => 'OB1234567',
                'label_attributes' => [
                    'class' => 'govuk-label govuk-radios__label govuk-label--s',
                ],
                'hint' => 'Restricted (North East of England)',
                'selected' => true,
                'attributes' => [
                    'id' => 'licence'
                ]
            ],
        ];

        $mockForm = m::mock(Form::class);

        $mockForm
            ->shouldReceive('get->get->setValueOptions')
            ->with($valueOptions)
            ->once();
        $mockForm
            ->shouldReceive('get->get->setAttribute')
            ->once()
            ->with(
                'radios_wrapper_attributes',
                ['class' => 'visually-hidden']
            );
        $mockForm
            ->shouldReceive('get->add')
            ->once()
            ->with(m::type(HtmlTranslated::class))
            ->andReturnUsing(
                function (HtmlTranslated $htmlTranslated) {
                    $this->assertEquals('7Content', $htmlTranslated->getName());
                    $this->assertEquals(
                        LicencesAvailable::ECMT_RESTRICTED_HINT,
                        $htmlTranslated->getValue()
                    );

                    return $htmlTranslated;
                }
            );

        $this->assertEquals(
            $outputData,
            $this->licencesAvailable->mapForFormOptions($inputData, $mockForm)
        );
    }

    /**
     * @dataProvider dpTestMapForFormOptions
     */
    public function testMapForFormOptions($inputData, $outputData, $valueOptions)
    {
        $mockForm = m::mock(Form::class);

        $mockForm
            ->shouldReceive('get->get->setValueOptions')
            ->with($valueOptions)
            ->once();

        $mockForm->allows('get->add');
        $mockForm->allows('get->get->setAttribute')
            ->with(
                'radios_wrapper_attributes',
                ['class' => 'visually-hidden']
            );

        $this->assertEquals(
            $outputData,
            $this->licencesAvailable->mapForFormOptions($inputData, $mockForm)
        );
    }

    public function dpTestMapForFormOptions()
    {
        return [
            'empty list' => [
                'inputData' => [
                    'licencesAvailable' => [
                        'eligibleLicences' => [
                            'result' => []
                        ]
                    ]
                ],
                'outputData' => [
                    'licencesAvailable' => [
                        'eligibleLicences' => [
                            'result' => []
                        ]
                    ]
                ],
                'expectedValueOptions' => [],
            ],
            '2 licences available for selection' => [
                'inputData' => [
                    'irhpPermitType' => [
                        'id' => RefData::ECMT_PERMIT_TYPE_ID,
                    ],
                    'licencesAvailable' => [
                        'eligibleLicences' => [
                            'result' => [
                                0 => [
                                    'id' => 7,
                                    'licNo' => 'OB1234567',
                                    'trafficArea' => 'North East of England',
                                    'licenceType' => [
                                        'description' => 'Standard International',
                                        'id' => 'ltyp_si',
                                    ],
                                    'canMakeEcmtApplication' => true,
                                ],
                                1 => [
                                    'id' => 70,
                                    'licNo' => 'OG7654321',
                                    'trafficArea' => 'Wales',
                                    'licenceType' => [
                                        'description' => 'Standard International',
                                        'id' => 'ltyp_si',
                                    ],
                                    'canMakeEcmtApplication' => true,
                                ],
                                2 => [
                                    'id' => 703,
                                    'licNo' => 'OG9654321',
                                    'trafficArea' => 'Wales',
                                    'licenceType' => [
                                        'description' => 'Standard International',
                                        'id' => 'ltyp_si',
                                    ],
                                    'canMakeEcmtApplication' => false,
                                ]
                            ]
                        ]
                    ]
                ],
                'outputData' => [
                    'irhpPermitType' => [
                        'id' => RefData::ECMT_PERMIT_TYPE_ID,
                    ],
                    'licencesAvailable' => [
                        'eligibleLicences' => [
                            'result' => [
                                0 => [
                                    'id' => 7,
                                    'licNo' => 'OB1234567',
                                    'trafficArea' => 'North East of England',
                                    'licenceType' => [
                                        'description' => 'Standard International',
                                        'id' => 'ltyp_si',
                                    ],
                                    'canMakeEcmtApplication' => true,
                                ],
                                1 => [
                                    'id' => 70,
                                    'licNo' => 'OG7654321',
                                    'trafficArea' => 'Wales',
                                    'licenceType' => [
                                        'description' => 'Standard International',
                                        'id' => 'ltyp_si',
                                    ],
                                    'canMakeEcmtApplication' => true,
                                ],
                                2 => [
                                    'id' => 703,
                                    'licNo' => 'OG9654321',
                                    'trafficArea' => 'Wales',
                                    'licenceType' => [
                                        'description' => 'Standard International',
                                        'id' => 'ltyp_si',
                                    ],
                                    'canMakeEcmtApplication' => false,
                                ]
                            ]
                        ]
                    ],
                ],
                'expectedValueOptions' => $this->standardValueOptions(),
            ],
            'multilateral permits standard' => [
                'inputData' => $this->standardInput(RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID),
                'outputData' => $this->standardOutput(RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID),
                'expectedValueOptions' => $this->standardValueOptions(),
            ],
            'bilateral permits standard' => [
                'inputData' => $this->standardInput(RefData::IRHP_BILATERAL_PERMIT_TYPE_ID),
                'outputData' => $this->standardOutput(RefData::IRHP_BILATERAL_PERMIT_TYPE_ID),
                'expectedValueOptions' => $this->standardValueOptions(),
            ],
            'ecmt removals standard' => [
                'inputData' => $this->standardInput(RefData::ECMT_REMOVAL_PERMIT_TYPE_ID),
                'outputData' => $this->standardOutput(RefData::ECMT_REMOVAL_PERMIT_TYPE_ID),
                'expectedValueOptions' => $this->standardValueOptions(),
            ],
            'multilateral permits already applied' => [
                'inputData' => $this->alreadyAppliedInput(RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID),
                'outputData' => $this->alreadyAppliedOutput(RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID),
                'expectedValueOptions' => $this->alreadyAppliedValueOptions(),
            ],
            'bilateral permits already applied' => [
                'inputData' => $this->alreadyAppliedInput(RefData::IRHP_BILATERAL_PERMIT_TYPE_ID),
                'outputData' => $this->alreadyAppliedOutput(RefData::IRHP_BILATERAL_PERMIT_TYPE_ID),
                'expectedValueOptions' => $this->alreadyAppliedValueOptions(),
            ],
            'ecmt removals already applied' => [
                'inputData' => $this->alreadyAppliedInput(RefData::ECMT_REMOVAL_PERMIT_TYPE_ID),
                'outputData' => $this->alreadyAppliedOutput(RefData::ECMT_REMOVAL_PERMIT_TYPE_ID),
                'expectedValueOptions' => $this->alreadyAppliedValueOptions(),
            ],
            'ecmt annual already applied' => [
                'inputData' => $this->alreadyAppliedInput(RefData::ECMT_PERMIT_TYPE_ID),
                'outputData' => $this->alreadyAppliedOutputAnnualEcmt(RefData::ECMT_PERMIT_TYPE_ID),
                'expectedValueOptions' => $this->alreadyAppliedValueOptions(),
            ],
        ];
    }

    private function standardInput(int $permitTypeId): array
    {
        return [
            'irhpPermitType' => [
                'id' => $permitTypeId,
            ],
            'licencesAvailable' => [
                'eligibleLicences' => [
                    'result' => [
                        0 => [
                            'id' => 7,
                            'licNo' => 'OB1234567',
                            'trafficArea' => 'North East of England',
                            'licenceType' => [
                                'description' => 'Standard International',
                                'id' => 'ltyp_si',
                            ],
                            'canMakeEcmtApplication' => true,
                        ],
                        1 => [
                            'id' => 70,
                            'licNo' => 'OG7654321',
                            'trafficArea' => 'Wales',
                            'licenceType' => [
                                'description' => 'Standard International',
                                'id' => 'ltyp_si',
                            ],
                            'canMakeEcmtApplication' => true,
                        ]
                    ]
                ]
            ]
        ];
    }

    private function standardOutput(int $permitTypeId): array
    {
        return [
            'irhpPermitType' => [
                'id' => $permitTypeId,
            ],
            'licencesAvailable' => [
                'eligibleLicences' => [
                    'result' => [
                        0 => [
                            'id' => 7,
                            'licNo' => 'OB1234567',
                            'trafficArea' => 'North East of England',
                            'licenceType' => [
                                'description' => 'Standard International',
                                'id' => 'ltyp_si',
                            ],
                            'canMakeEcmtApplication' => true,
                        ],
                        1 => [
                            'id' => 70,
                            'licNo' => 'OG7654321',
                            'trafficArea' => 'Wales',
                            'licenceType' => [
                                'description' => 'Standard International',
                                'id' => 'ltyp_si',
                            ],
                            'canMakeEcmtApplication' => true,
                        ]
                    ]
                ]
            ]
        ];
    }

    private function standardValueOptions(): array
    {
        return [
            7 => [
                'value' => 7,
                'label' => 'OB1234567',
                'label_attributes' => [
                    'class' => 'govuk-label govuk-radios__label govuk-label--s',
                ],
                'hint' => 'Standard International (North East of England)',
                'selected' => false,
                'attributes' => [
                    'id' => 'licence'
                ],
            ],
            70 => [
                'value' => 70,
                'label' => 'OG7654321',
                'label_attributes' => [
                    'class' => 'govuk-label govuk-radios__label govuk-label--s',
                ],
                'hint' => 'Standard International (Wales)',
                'selected' => false,
            ]
        ];
    }

    private function alreadyAppliedInput(int $irhpPermitTypeId): array
    {
        return [
            'irhpPermitType' => [
                'id' => $irhpPermitTypeId,
            ],
            'licencesAvailable' => [
                'eligibleLicences' => [
                    'result' => [
                        0 => [
                            'id' => 7,
                            'licNo' => 'OB1234567',
                            'trafficArea' => 'North East of England',
                            'licenceType' => [
                                'description' => 'Restricted',
                                'id' => 'ltyp_r',
                            ],
                            'canMakeEcmtApplication' => true,
                        ],
                    ]
                ]
            ],
            'active' => 7,
        ];
    }

    private function alreadyAppliedOutput(int $irhpPermitTypeId): array
    {
        return [
            'irhpPermitType' => [
                'id' => $irhpPermitTypeId,
            ],
            'licencesAvailable' => [
                'eligibleLicences' => [
                    'result' => [
                        0 => [
                            'id' => 7,
                            'licNo' => 'OB1234567',
                            'trafficArea' => 'North East of England',
                            'licenceType' => [
                                'description' => 'Restricted',
                                'id' => 'ltyp_r',
                            ],
                            'canMakeEcmtApplication' => true,
                        ],
                    ]
                ]
            ],
            'active' => 7,
            'warning' => 'permits.irhp.bilateral.already-applied',
        ];
    }

    private function alreadyAppliedOutputAnnualEcmt(int $irhpPermitTypeId): array
    {
        return [
            'irhpPermitType' => [
                'id' => $irhpPermitTypeId,
            ],
            'licencesAvailable' => [
                'eligibleLicences' => [
                    'result' => [
                        0 => [
                            'id' => 7,
                            'licNo' => 'OB1234567',
                            'trafficArea' => 'North East of England',
                            'licenceType' => [
                                'description' => 'Restricted',
                                'id' => 'ltyp_r',
                            ],
                            'canMakeEcmtApplication' => true,
                        ],
                    ]
                ]
            ],
            'active' => 7,
            'warning' => 'permits.irhp.bilateral.already-applied',
            'question' => 'permits.page.licence.question.one.licence',
            'questionArgs' => ['OB1234567 Restricted (North East of England)']
        ];
    }

    private function alreadyAppliedValueOptions(): array
    {
        return [
            7 => [
                'value' => 7,
                'label' => 'OB1234567',
                'label_attributes' => [
                    'class' => 'govuk-label govuk-radios__label govuk-label--s',
                ],
                'hint' => 'Restricted (North East of England)',
                'selected' => true,
                'attributes' => [
                    'id' => 'licence'
                ],
            ],
        ];
    }
}
