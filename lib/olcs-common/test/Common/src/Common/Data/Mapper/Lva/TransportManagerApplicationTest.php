<?php

namespace CommonTest\Data\Mapper\Lva;

use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Laminas\Form\FormInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Data\Mapper\Lva\TransportManagerApplication;

class TransportManagerApplicationTest extends MockeryTestCase
{
    public function testMapFromError(): void
    {
        $formMessages = [
            'data' => [
                'registeredUser' => [['error']]
            ]
        ];
        $globalMessages = [
            'global' => ['message']
        ];
        $messages = [
            'registeredUser' => ['error'],
            'global' => ['message']
        ];
        $mockForm = m::mock(FormInterface::class)
            ->shouldReceive('setMessages')
            ->with($formMessages)
            ->once()
            ->getMock();

        $errors = TransportManagerApplication::mapFromErrors($mockForm, $messages);
        $this->assertEquals($errors, $globalMessages);
    }

    /**
     * testMapForSections
     *
     * @param $data
     *
     * @dataProvider transportManagerDataProvider
     */
    public function testMapForSections($data): void
    {
        $translationHelper = m::mock(TranslationHelperService::class);

        $translationHelper->shouldReceive(
            'translateReplace'
        )->twice()->andReturn('__TEST__');
        $translationHelper->shouldReceive(
            'translate'
        )->times(23)->andReturn('__TEST__');
        $data = TransportManagerApplication::mapForSections($data, $translationHelper);
        $this->assertIsArray($data);
    }

    /**
     * @return ((((string|string[])[]|string)[]|string)[]|string)[][][]
     *
     * @psalm-return list{list{array{application: array{vehicleType: array{id: 'app_veh_type_mixed'}}, isOwner: '__TEST__', tmType: array{description: '__TEST__'}, hoursMon: '__TEST__', hoursTue: '__TEST__', hoursWed: '__TEST__', hoursThu: '__TEST__', hoursFri: '__TEST__', hoursSat: '__TEST__', hoursSun: '__TEST__', otherLicences: array<never, never>, additionalInformation: '__TEST__', hasUndertakenTraining: 'N', transportManager: array{otherLicences: array<never, never>, employments: array<never, never>, previousConvictions: array<never, never>, documents: array<never, never>, homeCd: array{emailAddress: '__TEST__', address: array{countryCode: array{countryDesc: '__TEST__'}}, person: array{forename: '__TEST__', familyName: '__TEST__'}}, workCd: array{address: array{countryCode: array{countryDesc: '__TEST__'}}}}}}}
     */
    public function transportManagerDataProvider(): array
    {
        return [
            [
                [
                    'application' => [
                        'vehicleType' => [
                            'id' => RefData::APP_VEHICLE_TYPE_MIXED,
                        ],
                    ],
                    'isOwner' => '__TEST__',
                    'tmType' => ['description' => '__TEST__'],
                    'hoursMon' => '__TEST__',
                    'hoursTue' => '__TEST__',
                    'hoursWed' => '__TEST__',
                    'hoursThu' => '__TEST__',
                    'hoursFri' => '__TEST__',
                    'hoursSat' => '__TEST__',
                    'hoursSun' => '__TEST__',
                    'otherLicences' => [
                    ],
                    'additionalInformation' => '__TEST__',
                    'hasUndertakenTraining' => 'N',
                    'transportManager' =>
                        [
                            'otherLicences' => [],
                            'employments' => [],
                            'previousConvictions' => [],
                            'documents' => [],
                            'homeCd' => [
                                'emailAddress' => '__TEST__',
                                'address' => [
                                    'countryCode' => [
                                        'countryDesc' => '__TEST__'
                                    ],
                                ],
                                'person' => [
                                    'forename' => '__TEST__',
                                    'familyName' => '__TEST__',
                                ]
                            ],
                            'workCd' => [
                                'address' => [
                                    'countryCode' => [
                                        'countryDesc' => '__TEST__'
                                    ],
                                ]
                            ]
                        ]
                ]
            ]
        ];
    }
}
