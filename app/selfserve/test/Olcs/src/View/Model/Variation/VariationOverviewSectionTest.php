<?php

/**
 * Variation Overview Section Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\View\Model\Variation;

use Common\RefData;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\View\Model\Variation\VariationOverviewSection;
use Laminas\View\Model\ViewModel;

/**
 * Variation Overview Section Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationOverviewSectionTest extends MockeryTestCase
{
    public function testViewWithRequiresAttention()
    {
        $sectionDetails = ['status' => RefData::VARIATION_STATUS_REQUIRES_ATTENTION];
        $ref = 'type_of_licence';
        $data = [
            'id' => 1,
            'idIndex' => 'application',
            'sectionNumber' => 1,
        ];

        $viewModel = new VariationOverviewSection($ref, $data, $sectionDetails);

        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertEquals('section.name.type_of_licence', $viewModel->getVariable('name'));
        $this->assertEquals('orange', $viewModel->getVariable('statusColour'));
        $this->assertEquals('REQUIRES ATTENTION', $viewModel->getVariable('status'));

        // variation sections should NOT be visibly numbered
        $this->assertNull($viewModel->getVariable('sectionNumber')); // OLCS-7016;
    }

    public function testViewWithUpdated()
    {
        $sectionDetails = ['status' => RefData::VARIATION_STATUS_UPDATED];
        $ref = 'type_of_licence';
        $data = [
            'id' => 1,
            'idIndex' => 'application',
            'sectionNumber' => 1,
        ];

        $viewModel = new VariationOverviewSection($ref, $data, $sectionDetails);

        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertEquals('section.name.type_of_licence', $viewModel->getVariable('name'));
        $this->assertEquals('green', $viewModel->getVariable('statusColour'));
        $this->assertEquals('UPDATED', $viewModel->getVariable('status'));

        $this->assertNull($viewModel->getVariable('sectionNumber'));
    }

    public function testViewWithUnchanged()
    {
        $sectionDetails = ['status' => RefData::VARIATION_STATUS_UNCHANGED];
        $ref = 'type_of_licence';
        $data = [
            'id' => 1,
            'idIndex' => 'application',
            'sectionNumber' => 1,
        ];

        $viewModel = new VariationOverviewSection($ref, $data, $sectionDetails);

        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertEquals('section.name.type_of_licence', $viewModel->getVariable('name'));
        $this->assertEquals('', $viewModel->getVariable('statusColour'));
        $this->assertEquals('', $viewModel->getVariable('status'));

        $this->assertNull($viewModel->getVariable('sectionNumber'));
    }

    public function testViewWithNotEnabled()
    {
        $sectionDetails = ['status' => RefData::VARIATION_STATUS_UNCHANGED, 'enabled' => 0];
        $ref = 'type_of_licence';
        $data = [
            'id' => 1,
            'idIndex' => 'application',
            'sectionNumber' => 1,
        ];

        $viewModel = new VariationOverviewSection($ref, $data, $sectionDetails);

        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertEquals('section.name.type_of_licence', $viewModel->getVariable('name'));
        $this->assertEquals('', $viewModel->getVariable('statusColour'));
        $this->assertEquals('', $viewModel->getVariable('status'));
        $this->assertEquals(0, $viewModel->getVariable('enabled'));

        $this->assertNull($viewModel->getVariable('sectionNumber'));
    }

    /**
     * @dataProvider dpTestViewForPeople
     */
    public function testViewForPeople($data)
    {
        $sectionDetails = ['status' => RefData::VARIATION_STATUS_REQUIRES_ATTENTION];
        $ref = 'people';

        $viewModel = new VariationOverviewSection($ref, $data, $sectionDetails);

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
        $sectionDetails = ['status' => RefData::VARIATION_STATUS_REQUIRES_ATTENTION];
        $ref = 'operating_centres';

        $viewModel = new VariationOverviewSection($ref, $data, $sectionDetails);

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
                ],
                'expected' => 'section.name.operating_centres',
            ],
            'vehicleType set to LGV' => [
                'data' => [
                    'id' => 1,
                    'idIndex' => 'application',
                    'sectionNumber' => 1,
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
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_MIXED,
                    ],
                ],
                'expected' => 'section.name.operating_centres',
            ],
        ];
    }
}
