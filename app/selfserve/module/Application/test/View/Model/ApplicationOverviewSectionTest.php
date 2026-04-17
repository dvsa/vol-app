<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Application\View\Model;

use Common\RefData;
use Dvsa\Olcs\Application\View\Model\ApplicationOverviewSection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\View\Model\ViewModel;

class ApplicationOverviewSectionTest extends MockeryTestCase
{
    public function testViewWithRequiresAttention(): void
    {
        $sectionDetails = ['enabled' => true];
        $ref = 'type_of_licence';
        $data = [
            'id' => 1,
            'idIndex' => 'application',
            'sectionNumber' => 1,
            'applicationCompletion' => [
                'typeOfLicenceStatus' => 1
            ],
        ];

        $viewModel = new ApplicationOverviewSection($ref, $data, $sectionDetails);

        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertEquals('section.name.type_of_licence', $viewModel->getVariable('name'));
        $this->assertEquals('orange', $viewModel->getVariable('statusColour'));
        $this->assertEquals('Incomplete', $viewModel->getVariable('status'));
        $this->assertTrue($viewModel->getVariable('enabled'));
        $this->assertEquals(1, $viewModel->getVariable('sectionNumber'));
    }

    public function testViewWithUpdated(): void
    {
        $sectionDetails = ['enabled' => true];
        $ref = 'type_of_licence';
        $data = [
            'id' => 1,
            'idIndex' => 'application',
            'sectionNumber' => 1,
            'applicationCompletion' => [
                'typeOfLicenceStatus' => 2
            ],
        ];

        $viewModel = new ApplicationOverviewSection($ref, $data, $sectionDetails);

        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertEquals('section.name.type_of_licence', $viewModel->getVariable('name'));
        $this->assertEquals('green', $viewModel->getVariable('statusColour'));
        $this->assertEquals('Complete', $viewModel->getVariable('status'));
        $this->assertTrue($viewModel->getVariable('enabled'));
        $this->assertEquals(1, $viewModel->getVariable('sectionNumber'));
    }

    public function testViewWithUnchanged(): void
    {
        $sectionDetails = ['enabled' => false];
        $ref = 'type_of_licence';
        $data = [
            'id' => 1,
            'idIndex' => 'application',
            'sectionNumber' => 1,
            'applicationCompletion' => [
                'typeOfLicenceStatus' => 0
            ],
        ];

        $viewModel = new ApplicationOverviewSection($ref, $data, $sectionDetails);

        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertEquals('section.name.type_of_licence', $viewModel->getVariable('name'));
        $this->assertEquals('grey', $viewModel->getVariable('statusColour'));
        $this->assertEquals('Not Started', $viewModel->getVariable('status'));
        $this->assertFalse($viewModel->getVariable('enabled'));
        $this->assertEquals(1, $viewModel->getVariable('sectionNumber'));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestViewForPeople')]
    public function testViewForPeople($data): void
    {
        $sectionDetails = ['enabled' => true];
        $ref = 'people';

        $viewModel = new ApplicationOverviewSection($ref, $data, $sectionDetails);

        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertEquals('section.name.people.org_t_llp', $viewModel->getVariable('name'));
    }

    /**
     * @return (((string|string[])[]|int)[]|int|string)[][][]
     *
     * @psalm-return array{'org type from licence': array{data: array{id: 1, idIndex: 'application', sectionNumber: 1, applicationCompletion: array{peopleStatus: 1}, licence: array{organisation: array{type: array{id: 'org_t_llp'}}}}}, 'org type from organisation': array{data: array{id: 1, idIndex: 'application', sectionNumber: 1, applicationCompletion: array{peopleStatus: 1}, organisation: array{type: array{id: 'org_t_llp'}}}}}
     */
    public static function dpTestViewForPeople(): array
    {
        return [
            'org type from licence' => [
                'data' => [
                    'id' => 1,
                    'idIndex' => 'application',
                    'sectionNumber' => 1,
                    'applicationCompletion' => [
                        'peopleStatus' => 1
                    ],
                    'licence' => [
                        'organisation' => [
                            'type' => [
                                'id' => 'org_t_llp'
                            ]
                        ]
                    ]
                ],
            ],
            'org type from organisation' => [
                'data' => [
                    'id' => 1,
                    'idIndex' => 'application',
                    'sectionNumber' => 1,
                    'applicationCompletion' => [
                        'peopleStatus' => 1
                    ],
                    'organisation' => [
                        'type' => [
                            'id' => 'org_t_llp'
                        ]
                    ]
                ],
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestViewForOperatingCentres')]
    public function testViewForOperatingCentres($data, $expected): void
    {
        $sectionDetails = ['enabled' => true];
        $ref = 'operating_centres';

        $viewModel = new ApplicationOverviewSection($ref, $data, $sectionDetails);

        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertEquals($expected, $viewModel->getVariable('name'));
    }

    /**
     * @return (((int|string)[]|int|string)[]|string)[][]
     *
     * @psalm-return array{'vehicleType not set': array{data: array{id: 1, idIndex: 'application', sectionNumber: 1, applicationCompletion: array{operatingCentresStatus: 1}}, expected: 'section.name.operating_centres'}, 'vehicleType set to LGV': array{data: array{id: 1, idIndex: 'application', sectionNumber: 1, applicationCompletion: array{operatingCentresStatus: 1}, vehicleType: array{id: 'app_veh_type_lgv'}}, expected: 'section.name.operating_centres.lgv'}, 'vehicleType set to mixed': array{data: array{id: 1, idIndex: 'application', sectionNumber: 1, applicationCompletion: array{operatingCentresStatus: 1}, vehicleType: array{id: 'app_veh_type_mixed'}}, expected: 'section.name.operating_centres'}}
     */
    public static function dpTestViewForOperatingCentres(): array
    {
        return [
            'vehicleType not set' => [
                'data' => [
                    'id' => 1,
                    'idIndex' => 'application',
                    'sectionNumber' => 1,
                    'applicationCompletion' => [
                        'operatingCentresStatus' => 1
                    ],
                ],
                'expected' => 'section.name.operating_centres',
            ],
            'vehicleType set to LGV' => [
                'data' => [
                    'id' => 1,
                    'idIndex' => 'application',
                    'sectionNumber' => 1,
                    'applicationCompletion' => [
                        'operatingCentresStatus' => 1
                    ],
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_LGV,
                    ],
                ],
                'expected' => 'section.name.operating_centres.lgv',
            ],
            'vehicleType set to mixed' => [
                'data' => [
                    'id' => 1,
                    'idIndex' => 'application',
                    'sectionNumber' => 1,
                    'applicationCompletion' => [
                        'operatingCentresStatus' => 1
                    ],
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_MIXED,
                    ],
                ],
                'expected' => 'section.name.operating_centres',
            ],
        ];
    }
}
