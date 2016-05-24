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
                            'status' => ['id' => LicenceEntityService::LICENCE_STATUS_VALID],
                            'licenceType' => ['id' => 'type'],
                            'licNo' => '123',
                            'trafficArea' => ['name' => 'foo']
                        ]
                    ],
                    'applications' => [
                        [
                            'status' => [
                                'id' => ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION
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
                                'id' => ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION
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
                        'status' => ['id' => LicenceEntityService::LICENCE_STATUS_VALID],
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
                        'status' => ['id' => ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION],
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
                        'status' => ['id' => ApplicationEntityService::APPLICATION_STATUS_UNDER_CONSIDERATION],
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
