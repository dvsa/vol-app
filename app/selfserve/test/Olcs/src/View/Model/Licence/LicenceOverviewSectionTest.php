<?php

/**
 * Licence Overview Section Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace OlcsTest\View\Model\Licence;

use Common\RefData;
use Laminas\View\Model\ViewModel;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\View\Model\Licence\LicenceOverviewSection;

/**
 * Licence Overview Section Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceOverviewSectionTest extends MockeryTestCase
{
    /**
     * @dataProvider dpTestViewForPeople
     */
    public function testViewForPeople($data)
    {
        $ref = 'people';

        $viewModel = new LicenceOverviewSection($ref, $data);

        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertEquals('section.name.people.org_t_llp', $viewModel->getVariable('name'));
        $this->assertEquals($ref, $viewModel->getVariable('anchorRef'));
    }

    public function dpTestViewForPeople()
    {
        return [
            'org type from licence' => [
                'data' => [
                    'id' => 1,
                    'idIndex' => 'licence',
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
                    'idIndex' => 'licence',
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
        $ref = 'operating_centres';

        $viewModel = new LicenceOverviewSection($ref, $data);

        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertEquals($expected, $viewModel->getVariable('name'));
    }

    public function dpTestViewForOperatingCentres()
    {
        return [
            'vehicleType not set' => [
                'data' => [
                    'id' => 1,
                    'idIndex' => 'licence',
                    'sectionNumber' => 1,
                ],
                'expected' => 'section.name.operating_centres',
            ],
            'vehicleType set to LGV' => [
                'data' => [
                    'id' => 1,
                    'idIndex' => 'licence',
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
                    'idIndex' => 'licence',
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
