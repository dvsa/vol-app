<?php

namespace Dvsa\Olcs\Application\View\Model;

use Common\RefData;
use Dvsa\Olcs\Application\View\Model\ApplicationOverviewSection;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\View\Model\ViewModel;

class ApplicationOverviewSectionTest extends MockeryTestCase
{
    public function testViewWithRequiresAttention()
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
        $this->assertEquals('INCOMPLETE', $viewModel->getVariable('status'));
        $this->assertTrue($viewModel->getVariable('enabled'));
        $this->assertEquals(1, $viewModel->getVariable('sectionNumber'));
    }

    public function testViewWithUpdated()
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
        $this->assertEquals('COMPLETE', $viewModel->getVariable('status'));
        $this->assertTrue($viewModel->getVariable('enabled'));
        $this->assertEquals(1, $viewModel->getVariable('sectionNumber'));
    }

    public function testViewWithUnchanged()
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
        $this->assertEquals('NOT STARTED', $viewModel->getVariable('status'));
        $this->assertFalse($viewModel->getVariable('enabled'));
        $this->assertEquals(1, $viewModel->getVariable('sectionNumber'));
    }

    /**
     * @dataProvider dpTestViewForPeople
     */
    public function testViewForPeople($data)
    {
        $sectionDetails = ['enabled' => true];
        $ref = 'people';

        $viewModel = new ApplicationOverviewSection($ref, $data, $sectionDetails);

        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertEquals('section.name.people.org_t_llp', $viewModel->getVariable('name'));
    }

    public function dpTestViewForPeople()
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

    /**
     * @dataProvider dpTestViewForOperatingCentres
     */
    public function testViewForOperatingCentres($data, $expected)
    {
        $sectionDetails = ['enabled' => true];
        $ref = 'operating_centres';

        $viewModel = new ApplicationOverviewSection($ref, $data, $sectionDetails);

        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertEquals($expected, $viewModel->getVariable('name'));
    }

    public function dpTestViewForOperatingCentres()
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
