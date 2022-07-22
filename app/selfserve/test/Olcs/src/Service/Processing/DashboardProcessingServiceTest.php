<?php

namespace OlcsTest\View\Model;

use Common\RefData;
use Common\Service\Table\TableFactory;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Olcs\Service\Processing\DashboardProcessingService;

/**
 * DashboardProcessingServiceTest
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class DashboardProcessingServiceTest extends MockeryTestCase
{
    /**
     * Test get tables
     *
     * @dataProvider applicationsProvider
     * @group externalDashboard
     */
    public function testGetTables($data, $licences, $variations, $applications)
    {
        $tableService = m::mock(TableFactory::class);
        $tableService->shouldReceive('buildTable')
            ->with('dashboard-licences', $licences)
            ->once()
            ->andReturn($licences)
            ->shouldReceive('buildTable')
            ->with('dashboard-applications', $applications)
            ->once()
            ->andReturn($applications)
            ->shouldReceive('buildTable')
            ->with('dashboard-variations', $variations)
            ->once()
            ->andReturn($variations);

        $sut = new DashboardProcessingService($tableService);
        $result = $sut->getTables($data);

        $this->assertEquals($licences, $result['licences']);
        $this->assertEquals($applications, $result['applications']);
        $this->assertEquals($variations, $result['variations']);
    }

    /**
     * Applications provider
     *
     */
    public function applicationsProvider()
    {
        return [
            'empty data' => [
                [
                    'licences' => [],
                    'applications' => [],
                    'variations' => [],
                ],
                [],
                [],
                []
            ],
            'sample data' => [
                // source data
                [
                    'licences' => [
                        [
                            'id' => 1,
                            'status' => ['id' => RefData::LICENCE_STATUS_VALID],
                            'licenceType' => ['id' => 'type'],
                            'licNo' => '123',
                            'trafficArea' => ['name' => 'foo']
                        ]
                    ],
                    'applications' => [
                        [
                            'status' => [
                                'id' => RefData::APPLICATION_STATUS_UNDER_CONSIDERATION
                            ],
                            'isVariation' => false,
                            'id' => 1,
                            'licenceType' => ['id' => 'type'],
                            'licence' => [
                                'licNo' => '123',
                            ],
                        ],
                    ],
                    'variations' => [
                        [
                            'status' => [
                                'id' => RefData::APPLICATION_STATUS_UNDER_CONSIDERATION
                            ],
                            'isVariation' => true,
                            'id' => 2,
                            'licenceType' => ['id' => 'type'],
                            'licence' => [
                                'licNo' => '123',
                            ],
                        ],
                    ],
                ],
                // licences
                [
                    [
                        'id' => 1,
                        'licenceType' => ['id' => 'type'],
                        'licNo' => '123',
                        'status' => ['id' => RefData::LICENCE_STATUS_VALID],
                        'type' => 'type',
                        'trafficArea' => 'foo'
                    ]
                ],
                // variations
                [
                    [
                        'isVariation' => true,
                        'id' => 2,
                        'licNo' => '123',
                        'status' => ['id' => RefData::APPLICATION_STATUS_UNDER_CONSIDERATION],
                        'licence' => [
                                'licNo' => '123',
                        ],
                        'licenceType' => ['id' => 'type'],
                        'type' => 'type',
                    ]
                ],
                // applications
                [
                    [
                        'isVariation' => false,
                        'id' => 1,
                        'licNo' => '123',
                        'status' => ['id' => RefData::APPLICATION_STATUS_UNDER_CONSIDERATION],
                        'licence' => [
                                'licNo' => '123',
                        ],
                        'licenceType' => ['id' => 'type'],
                        'type' => 'type',
                    ]
                ]
            ],
        ];
    }
}
