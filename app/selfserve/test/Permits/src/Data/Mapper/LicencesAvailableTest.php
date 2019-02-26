<?php

namespace PermitsTest\Data\Mapper;

use Common\Form\Form;
use Common\RefData;
use Mockery as m;
use Permits\Data\Mapper\LicencesAvailable;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

class LicencesAvailableTest extends TestCase
{
    /**
     * @dataProvider dpTestMapForFormOptions
     */
    public function testMapForFormOptions($data, $expected, $expectedValueOptions)
    {
        $mockForm = m::mock(Form::class);

        $mockForm->shouldReceive('get')
            ->with('fields')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('get')
            ->with('licence')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setValueOptions')
            ->with($expectedValueOptions)
            ->once();

        self::assertEquals($expected, LicencesAvailable::mapForFormOptions($data, $mockForm));
    }

    public function dpTestMapForFormOptions()
    {
        return [
            'empty list' => [
                'data' => [
                    'licencesAvailable' => [
                        'eligibleLicences' => [
                            'result' => []
                        ]
                    ]
                ],
                'expected' => [
                    'licencesAvailable' => [
                        'eligibleLicences' => [
                            'result' => []
                        ]
                    ]
                ],
                'expectedValueOptions' => [],
            ],
            '2 licences available for selection' => [
                'data' => [
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
                                    'totAuthVehicles' => 12,
                                    'licenceType' => [
                                        'description' => 'Standard International',
                                        'displayOrder' => null,
                                        'id' => 'ltyp_si',
                                        'olbsKey' => 'SI',
                                        'parent' => null,
                                        'refDataCategoryId' => 'lic_type',
                                        'version' => 1,
                                    ],
                                    'restricted' => false,
                                    'canMakeEcmtApplication' => true,
                                    'canMakeBilateralApplication' => false,
                                ],
                                1 => [
                                    'id' => 70,
                                    'licNo' => 'OG7654321',
                                    'trafficArea' => 'Wales',
                                    'totAuthVehicles' => 12,
                                    'licenceType' => [
                                        'description' => 'Standard International',
                                        'displayOrder' => null,
                                        'id' => 'ltyp_si',
                                        'olbsKey' => 'SI',
                                        'parent' => null,
                                        'refDataCategoryId' => 'lic_type',
                                        'version' => 1,
                                    ],
                                    'restricted' => false,
                                    'canMakeEcmtApplication' => true,
                                    'canMakeBilateralApplication' => false,
                                ],
                                2 => [
                                    'id' => 703,
                                    'licNo' => 'OG9654321',
                                    'trafficArea' => 'Wales',
                                    'totAuthVehicles' => 12,
                                    'licenceType' => [
                                        'description' => 'Standard International',
                                        'displayOrder' => null,
                                        'id' => 'ltyp_si',
                                        'olbsKey' => 'SI',
                                        'parent' => null,
                                        'refDataCategoryId' => 'lic_type',
                                        'version' => 1,
                                    ],
                                    'restricted' => false,
                                    'canMakeEcmtApplication' => false,
                                    'canMakeBilateralApplication' => false,
                                ]
                            ]
                        ]
                    ]
                ],
                'expected' => [
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
                                    'totAuthVehicles' => 12,
                                    'licenceType' => [
                                        'description' => 'Standard International',
                                        'displayOrder' => null,
                                        'id' => 'ltyp_si',
                                        'olbsKey' => 'SI',
                                        'parent' => null,
                                        'refDataCategoryId' => 'lic_type',
                                        'version' => 1,
                                    ],
                                    'restricted' => false,
                                    'canMakeEcmtApplication' => true,
                                    'canMakeBilateralApplication' => false,
                                ],
                                1 => [
                                    'id' => 70,
                                    'licNo' => 'OG7654321',
                                    'trafficArea' => 'Wales',
                                    'totAuthVehicles' => 12,
                                    'licenceType' => [
                                        'description' => 'Standard International',
                                        'displayOrder' => null,
                                        'id' => 'ltyp_si',
                                        'olbsKey' => 'SI',
                                        'parent' => null,
                                        'refDataCategoryId' => 'lic_type',
                                        'version' => 1,
                                    ],
                                    'restricted' => false,
                                    'canMakeEcmtApplication' => true,
                                    'canMakeBilateralApplication' => false,
                                ],
                                2 => [
                                    'id' => 703,
                                    'licNo' => 'OG9654321',
                                    'trafficArea' => 'Wales',
                                    'totAuthVehicles' => 12,
                                    'licenceType' => [
                                        'description' => 'Standard International',
                                        'displayOrder' => null,
                                        'id' => 'ltyp_si',
                                        'olbsKey' => 'SI',
                                        'parent' => null,
                                        'refDataCategoryId' => 'lic_type',
                                        'version' => 1,
                                    ],
                                    'restricted' => false,
                                    'canMakeEcmtApplication' => false,
                                    'canMakeBilateralApplication' => false,
                                ]
                            ]
                        ]
                    ],
                    'warning' => LicencesAvailable::ECMT_PREVIOUSLY_APPLIED_MESSAGE,
                ],
                'expectedValueOptions' => [
                    7 => [
                        'value' => 7,
                        'label' => 'OB1234567',
                        'label_attributes' => [
                            'class' => 'govuk-label govuk-radios__label govuk-label--s',
                        ],
                        'hint' => 'Standard International (North East of England)',
                        'selected' => false,
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
                ],
            ]
        ];
    }
}
