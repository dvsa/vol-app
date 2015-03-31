<?php

/**
 * Dashboard Model Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\View\Model;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\View\Model\Dashboard;
use Mockery as m;
use Common\Service\Entity\LicenceEntityService;
use Common\Service\Entity\ApplicationEntityService;

/**
 * Dashboard Model Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class DashboardTest extends MockeryTestCase
{
    /**
     * Test constructor with set variables
     *
     * @dataProvider applicationsProvider
     * @group externalDashboard
     */
    public function testSetApplications($data, $licences, $variations, $applications)
    {
        $viewModel = new Dashboard();
        $mockSl = m::mock('Zend\ServiceManager\ServiceLocatorInterface')
            ->shouldReceive('get')
            ->with('Helper\Url')
            ->andReturn('url')
            ->shouldReceive('get')
            ->with('Table')
            ->andReturn(
                m::mock()
                ->shouldReceive('buildTable')
                ->with('dashboard-licences', $licences, ['url' => 'url'], false)
                ->andReturn($licences)
                ->once()
                ->shouldReceive('buildTable')
                ->with('dashboard-applications', $applications, ['url' => 'url'], false)
                ->andReturn($applications)
                ->once()
                ->shouldReceive('buildTable')
                ->with('dashboard-variations', $variations, ['url' => 'url'], false)
                ->andReturn($variations)
                ->once()
                ->getMock()
            )
            ->getMock();

        $viewModel->setServiceLocator($mockSl);
        $viewModel->setApplications($data);
        $this->assertEquals($licences, $viewModel->getVariable('licences'));
        $this->assertEquals($applications, $viewModel->getVariable('applications'));
        $this->assertEquals($variations, $viewModel->getVariable('variations'));
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
