<?php

declare(strict_types=1);

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
    public function testViewWithRequiresAttention(): void
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
        $this->assertEquals('Requires attention', $viewModel->getVariable('status'));

        // variation sections should NOT be visibly numbered
        $this->assertNull($viewModel->getVariable('sectionNumber')); // OLCS-7016;
    }

    public function testViewWithUpdated(): void
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
        $this->assertEquals('Updated', $viewModel->getVariable('status'));

        $this->assertNull($viewModel->getVariable('sectionNumber'));
    }

    public function testViewWithUnchanged(): void
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

    public function testViewWithNotEnabled(): void
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestViewForPeople')]
    public function testViewForPeople(array $data): void
    {
        $sectionDetails = ['status' => RefData::VARIATION_STATUS_REQUIRES_ATTENTION];
        $ref = 'people';

        $viewModel = new VariationOverviewSection($ref, $data, $sectionDetails);

        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertEquals('section.name.people.org_t_llp', $viewModel->getVariable('name'));
    }

    /**
     * @return ((string|string[])[][]|int|string)[][][]
     *
     * @psalm-return array{'org type from licence': array{data: array{id: 1, idIndex: 'application', sectionNumber: 1, licence: array{organisation: array{type: array{id: 'org_t_llp'}}}}}, 'org type from organisation': array{data: array{id: 1, idIndex: 'application', sectionNumber: 1, organisation: array{type: array{id: 'org_t_llp'}}}}}
     */
    public static function dpTestViewForPeople(): array
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestViewForOperatingCentres')]
    public function testViewForOperatingCentres(array $data, string $expected): void
    {
        $sectionDetails = ['status' => RefData::VARIATION_STATUS_REQUIRES_ATTENTION];
        $ref = 'operating_centres';

        $viewModel = new VariationOverviewSection($ref, $data, $sectionDetails);

        $this->assertInstanceOf(ViewModel::class, $viewModel);
        $this->assertEquals($expected, $viewModel->getVariable('name'));
    }

    /**
     * @return ((int|string|string[])[]|string)[][]
     *
     * @psalm-return array{'vehicleType not set': array{data: array{id: 1, idIndex: 'application', sectionNumber: 1}, expected: 'section.name.operating_centres'}, 'vehicleType set to LGV': array{data: array{id: 1, idIndex: 'application', sectionNumber: 1, vehicleType: array{id: 'app_veh_type_lgv'}}, expected: 'section.name.operating_centres.lgv'}, 'vehicleType set to mixed': array{data: array{id: 1, idIndex: 'application', sectionNumber: 1, vehicleType: array{id: 'app_veh_type_mixed'}}, expected: 'section.name.operating_centres'}}
     */
    public static function dpTestViewForOperatingCentres(): array
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
