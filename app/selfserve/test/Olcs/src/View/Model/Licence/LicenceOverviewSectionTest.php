<?php

declare(strict_types=1);

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
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestViewForPeople')]
    public function testViewForPeople(array $data): void
    {
        $ref = 'people';

        $viewModel = new LicenceOverviewSection($ref, $data);

        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertEquals('section.name.people.org_t_llp', $viewModel->getVariable('name'));
        $this->assertEquals($ref, $viewModel->getVariable('anchorRef'));
    }

    /**
     * @return ((string|string[])[][]|int|string)[][][]
     *
     * @psalm-return array{'org type from licence': array{data: array{id: 1, idIndex: 'licence', sectionNumber: 1, licence: array{organisation: array{type: array{id: 'org_t_llp'}}}}}, 'org type from organisation': array{data: array{id: 1, idIndex: 'licence', sectionNumber: 1, organisation: array{type: array{id: 'org_t_llp'}}}}}
     */
    public static function dpTestViewForPeople(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestViewForOperatingCentres')]
    public function testViewForOperatingCentres(array $data, string $expected): void
    {
        $ref = 'operating_centres';

        $viewModel = new LicenceOverviewSection($ref, $data);

        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertEquals($expected, $viewModel->getVariable('name'));
    }

    /**
     * @return ((int|string|string[])[]|string)[][]
     *
     * @psalm-return array{'vehicleType not set': array{data: array{id: 1, idIndex: 'licence', sectionNumber: 1}, expected: 'section.name.operating_centres'}, 'vehicleType set to LGV': array{data: array{id: 1, idIndex: 'licence', sectionNumber: 1, vehicleType: array{id: 'app_veh_type_lgv'}}, expected: 'section.name.operating_centres.lgv'}, 'vehicleType set to mixed': array{data: array{id: 1, idIndex: 'licence', sectionNumber: 1, vehicleType: array{id: 'app_veh_type_mixed'}}, expected: 'section.name.operating_centres'}}
     */
    public static function dpTestViewForOperatingCentres(): array
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
