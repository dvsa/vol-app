<?php

/**
 * DashboardProcessingServiceTest
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace OlcsTest\View\Model;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;
use Common\Service\Entity\LicenceEntityService;
use Common\Service\Entity\ApplicationEntityService;

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
        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface');
        $mockSl->shouldReceive('get')
            ->with('Table')
            ->andReturn(
                m::mock()
                ->shouldReceive('buildTable')
                ->with('dashboard-licences', $licences)
                ->andReturn($licences)
                ->once()
                ->shouldReceive('buildTable')
                ->with('dashboard-applications', $applications)
                ->andReturn($applications)
                ->once()
                ->shouldReceive('buildTable')
                ->with('dashboard-variations', $variations)
                ->andReturn($variations)
                ->once()
                ->getMock()
            )
            ->getMock();

        $sut = new \Olcs\Service\Processing\DashboardProcessingService();
        $sut->setServiceLocator($mockSl);
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
            'empty data' => [[], [], [], []],
            'different applications' => [
                // source data
                [
                    'licences' => [
                        [
                            'id' => 1,
                            'status' => ['id' => LicenceEntityService::LICENCE_STATUS_VALID],
                            'licenceType' => ['id' => 'type'],
                            'licNo' => '123',
                            'applications' => [
                                [
                                    'status' => [
                                        'id' => ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION
                                    ],
                                    'isVariation' => false,
                                    'id' => 1
                                ],
                                [
                                    'status' => ['id' => ApplicationEntityService::APPLICATION_STATUS_VALID],
                                    'isVariation' => false,
                                    'id' => 2
                                ],
                            ]
                        ]
                    ]
                ],
                // licences
                [
                    1 => [
                        'id' => 1,
                        'status' => ['id' => LicenceEntityService::LICENCE_STATUS_VALID],
                        'licenceType' => ['id' => 'type'],
                        'licNo' => '123',
                        'status' => LicenceEntityService::LICENCE_STATUS_VALID,
                        'type' => 'type',
                        'applications' => [
                            [
                                'status' => ['id' => ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION],
                                'isVariation' => false,
                                'id' => 1
                            ],
                            [
                                'status' => ['id' => ApplicationEntityService::APPLICATION_STATUS_VALID],
                                'isVariation' => false,
                                'id' => 2
                            ],
                        ]
                    ]
                ],
                // variations
                [],
                // applications
                [
                    1 => [
                        'status' => ['id' => ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION],
                        'isVariation' => false,
                        'id' => 1,
                        'licNo' => '123',
                        'status' => ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION
                    ]
                ]
            ],
            'no licences' => [
                // source data
                [
                    'licences' => [
                        [
                            'id' => 1,
                            'status' => ['id' => LicenceEntityService::LICENCE_STATUS_GRANTED],
                            'licenceType' => ['id' => 'type'],
                            'licNo' => '123',
                            'applications' => [
                                [
                                    'status' => [
                                        'id' => ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION
                                    ],
                                    'isVariation' => false,
                                    'id' => 1
                                ],
                                [
                                    'status' => ['id' => ApplicationEntityService::APPLICATION_STATUS_VALID],
                                    'isVariation' => false,
                                    'id' => 2
                                ],
                            ]
                        ]
                    ]
                ],
                // licences
                [
                ],
                // variations
                [],
                // applications
                [
                    1 => [
                        'status' => ['id' => ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION],
                        'isVariation' => false,
                        'id' => 1,
                        'licNo' => '123',
                        'status' => ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION
                    ]
                ]
            ],
            'different variations' => [
                // source data
                [
                    'licences' => [
                        [
                            'id' => 1,
                            'status' => ['id' => LicenceEntityService::LICENCE_STATUS_GRANTED],
                            'licenceType' => ['id' => 'type'],
                            'licNo' => '123',
                            'applications' => [
                                [
                                    'status' => [
                                        'id' => ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION
                                    ],
                                    'isVariation' => true,
                                    'id' => 1
                                ],
                                [
                                    'status' => ['id' => ApplicationEntityService::APPLICATION_STATUS_VALID],
                                    'isVariation' => true,
                                    'id' => 2
                                ],
                            ]
                        ]
                    ]
                ],
                // licences
                [
                ],
                // variations
                [
                    1 => [
                        'status' => ['id' => ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION],
                        'isVariation' => true,
                        'id' => 1,
                        'licNo' => '123',
                        'status' => ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION
                    ]
                ],
                // applications
                []
            ]
        ];
    }
}
