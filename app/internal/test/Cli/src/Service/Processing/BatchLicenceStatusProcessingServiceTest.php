<?php

/**
 * Test Batch Processing Service
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
namespace CliTest\Service\Processing;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Cli\Service\Processing\BatchLicenceStatusProcessingService;
use Common\Service\Entity\LicenceEntityService;

/**
 * Test Batch Processing Service
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class BatchLicenceStatusProcessingServiceTest extends MockeryTestCase
{
    protected $sm;
    protected $sut;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();
        $this->sut = new BatchLicenceStatusProcessingService();
        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * Test setting and getting the console adapter
     */
    public function testGetSetConsoleAdapter()
    {
        $this->assertNull($this->sut->getConsoleAdapter());

        $mock = m::mock('\Zend\Console\Adapter\Posix');

        $this->sut->setConsoleAdapter($mock);

        $this->assertEquals($mock, $this->sut->getConsoleAdapter());
    }

    /**
     * Test output is generated when console adapter is set
     */
    public function testOutputGenerated()
    {
        $mockLicenceStatusHelperService = m::mock('\StdClass');
        $this->sm->setService('Helper\LicenceStatus', $mockLicenceStatusHelperService);

        $mockLicenceStatusRuleService = m::mock('\StdClass');
        $this->sm->setService('Entity\LicenceStatusRule', $mockLicenceStatusRuleService);

        $mockLicenceService = m::mock('\StdClass');
        $this->sm->setService('Entity\Licence', $mockLicenceService);

        $mockDateService = m::mock('\StdClass');
        $mockDateService->shouldReceive('getDate')->andReturn('2015-03-24');
        $this->sm->setService('Helper\Date', $mockDateService);

        $mockConsole = m::mock('\Zend\Console\Adapter\Posix');
        $this->sut->setConsoleAdapter($mockConsole);

        $mockLicenceStatusRuleService->shouldReceive('getLicencesToRevokeCurtailSuspend')
            ->andReturn([]);

        $mockConsole->shouldReceive('writeLine')->once();

        $this->sut->processToRevokeCurtailSuspend();
    }

    public function licenceStatusDataProvider()
    {
        return array(
            array('lsts_curtailed', 'curtailedDate'),
            array('lsts_suspended', 'suspendedDate')
        );
    }

    /**
     * @dataProvider licenceStatusDataProvider
     */
    public function testProcessToRevokeCurtailSuspendOnlyValidActioned($status, $column)
    {
        $mockLicenceStatusHelperService = m::mock('\StdClass');
        $this->sm->setService('Helper\LicenceStatus', $mockLicenceStatusHelperService);

        $mockLicenceStatusRuleService = m::mock('\StdClass');
        $this->sm->setService('Entity\LicenceStatusRule', $mockLicenceStatusRuleService);

        $mockLicenceService = m::mock('\StdClass');
        $this->sm->setService('Entity\Licence', $mockLicenceService);

        $mockDateService = m::mock('\StdClass');
        $mockDateService->shouldReceive('getDate')->andReturn('2015-03-24');
        $this->sm->setService('Helper\Date', $mockDateService);

        $getLicencesToRevokeCurtailSuspend = [
            [
                'id' => 65765,
                'licenceStatus' => [
                    'id' => $status
                ],
                'licence' => [
                    'id' => 1221,
                    'status' => [
                        'id' => LicenceEntityService::LICENCE_STATUS_VALID,
                        'description' => 'Foobar',
                    ]
                ]
            ],
        ];

        $mockLicenceStatusRuleService->shouldReceive('getLicencesToRevokeCurtailSuspend')
            ->andReturn($getLicencesToRevokeCurtailSuspend);

        $mockLicenceService->shouldReceive('forceUpdate')
            ->once()
            ->with(1221, ['status' => $status, $column => '2015-03-24']);

        $mockLicenceStatusRuleService->shouldReceive('forceUpdate')
            ->once()
            ->with(65765, ['startProcessedDate' => '2015-03-24']);

        $this->sut->processToRevokeCurtailSuspend();
    }

    /**
     * Test status updated for licence with status valid
     */
    public function testProcessToRevokeOnlyValidActioned()
    {
        $mockLicenceStatusHelperService = m::mock('\StdClass')
            ->shouldReceive('ceaseDiscs')
            ->shouldReceive('removeLicenceVehicles')
            ->shouldReceive('removeTransportManagers')
            ->getMock();
        $this->sm->setService('Helper\LicenceStatus', $mockLicenceStatusHelperService);

        $mockLicenceStatusRuleService = m::mock('\StdClass');
        $this->sm->setService('Entity\LicenceStatusRule', $mockLicenceStatusRuleService);

        $mockLicenceService = m::mock('\StdClass')
            ->shouldReceive('getRevocationDataForLicence')
            ->shouldReceive('forceUpdate')
            ->getMock();

        $this->sm->setService('Entity\Licence', $mockLicenceService);

        $mockDateService = m::mock('\StdClass');
        $mockDateService->shouldReceive('getDate')->andReturn('2015-03-24');
        $this->sm->setService('Helper\Date', $mockDateService);

        $getLicencesToRevokeCurtailSuspend = [
            [
                'id' => 65765,
                'licenceStatus' => [
                    'id' => 'lsts_revoked'
                ],
                'licence' => [
                    'id' => 1221,
                    'status' => [
                        'id' => LicenceEntityService::LICENCE_STATUS_VALID,
                        'description' => 'Foobar',
                    ]
                ]
            ],
        ];

        $mockLicenceStatusRuleService->shouldReceive('getLicencesToRevokeCurtailSuspend')
            ->andReturn($getLicencesToRevokeCurtailSuspend);

        $mockLicenceStatusRuleService->shouldReceive('forceUpdate')
            ->once()
            ->with(65765, ['startProcessedDate' => '2015-03-24']);

        $this->sut->processToRevokeCurtailSuspend();
    }

    /**
     * Test no updates done if status isn't "valid"
     */
    public function testProcessToRevokeCurtailSuspendOnlyNonValidIgnored()
    {
        $mockLicenceStatusHelperService = m::mock('\StdClass');
        $this->sm->setService('Helper\LicenceStatus', $mockLicenceStatusHelperService);

        $mockLicenceStatusRuleService = m::mock('\StdClass');
        $this->sm->setService('Entity\LicenceStatusRule', $mockLicenceStatusRuleService);

        $mockLicenceService = m::mock('\StdClass');
        $this->sm->setService('Entity\Licence', $mockLicenceService);

        $mockDateService = m::mock('\StdClass');
        $this->sm->setService('Helper\Date', $mockDateService);
        $mockDateService->shouldReceive('getDate')->andReturn('2015-03-24');

        $getLicencesToRevokeCurtailSuspend = [
            [
                'id' => 1,
                'licenceStatus' => [
                    'id' => 'status1'
                ],
                'licence' => [
                    'id' => 1,
                    'status' => [
                        'id' => LicenceEntityService::LICENCE_STATUS_SUSPENDED,
                        'description' => 'Foobar',
                    ]
                ]
            ],
            [
                'id' => 2,
                'licenceStatus' => [
                    'id' => 'status1'
                ],
                'licence' => [
                    'id' => 2,
                    'status' => [
                        'id' => LicenceEntityService::LICENCE_STATUS_CURTAILED,
                        'description' => 'Foobar',
                    ]
                ]
            ],
            [
                'id' => 3,
                'licenceStatus' => [
                    'id' => 'status1'
                ],
                'licence' => [
                    'id' => 3,
                    'status' => [
                        'id' => LicenceEntityService::LICENCE_STATUS_REVOKED,
                        'description' => 'Foobar',
                    ]
                ]
            ],
        ];

        $mockLicenceStatusRuleService->shouldReceive('getLicencesToRevokeCurtailSuspend')
            ->andReturn($getLicencesToRevokeCurtailSuspend);

        $mockLicenceService->shouldReceive('forceUpdate')->never();
        $mockLicenceStatusRuleService->shouldReceive('forceUpdate')->never();

        $this->sut->processToRevokeCurtailSuspend();
    }

    /**
     * Test status reset for licence with status suspended
     */
    public function testToValidSuspendedCurtailed()
    {
        $mockLicenceStatusHelperService = m::mock('\StdClass');
        $this->sm->setService('Helper\LicenceStatus', $mockLicenceStatusHelperService);

        $mockLicenceStatusRuleService = m::mock('\StdClass');
        $this->sm->setService('Entity\LicenceStatusRule', $mockLicenceStatusRuleService);

        $mockVehicleService = m::mock('\StdClass');
        $this->sm->setService('Entity\Vehicle', $mockVehicleService);

        $mockLicenceService = m::mock('\StdClass');
        $this->sm->setService('Entity\Licence', $mockLicenceService);

        $mockDateService = m::mock('\StdClass');
        $mockDateService->shouldReceive('getDate')->andReturn('2015-03-24');
        $this->sm->setService('Helper\Date', $mockDateService);

        $getLicencesToValid = [
            [
                'id' => 65765,
                'licence' => [
                    'id' => 1221,
                    'status' => [
                        'id' => LicenceEntityService::LICENCE_STATUS_SUSPENDED,
                        'description' => 'Foobar',
                    ],
                    'licenceVehicles' => [
                        [
                            'vehicle' => ['id' => 44],
                        ],
                        [
                            'vehicle' => ['id' => 75],
                        ],

                    ]
                ]
            ],
            [
                'id' => 2,
                'licence' => [
                    'id' => 3,
                    'status' => [
                        'id' => LicenceEntityService::LICENCE_STATUS_CURTAILED,
                        'description' => 'Foobar',
                    ],
                    'licenceVehicles' => [
                        [
                            'vehicle' => ['id' => 44],
                        ],
                        [
                            'vehicle' => ['id' => 75],
                        ],

                    ]
                ]
            ],
        ];

        $mockLicenceStatusRuleService->shouldReceive('getLicencesToValid')
            ->andReturn($getLicencesToValid);

        $mockVehicleService->shouldReceive('forceUpdate')
            ->twice()
            ->with(44, ['section26' => 0]);
        $mockVehicleService->shouldReceive('forceUpdate')
            ->twice()
            ->with(75, ['section26' => 0]);

        $mockLicenceService->shouldReceive('forceUpdate')
            ->once()
            ->with(
                1221,
                array(
                    'status' => LicenceEntityService::LICENCE_STATUS_VALID,
                    'revokedDate' => null,
                    'curtailedDate' => null,
                    'suspendedDate' => null
                )
            );
        $mockLicenceService->shouldReceive('forceUpdate')
            ->once()
            ->with(
                3,
                array(
                    'status' => LicenceEntityService::LICENCE_STATUS_VALID,
                    'revokedDate' => null,
                    'curtailedDate' => null,
                    'suspendedDate' => null
                )
            );

        $mockLicenceStatusRuleService->shouldReceive('forceUpdate')
            ->once()
            ->with(65765, ['endProcessedDate' => '2015-03-24']);
        $mockLicenceStatusRuleService->shouldReceive('forceUpdate')
            ->once()
            ->with(2, ['endProcessedDate' => '2015-03-24']);

        $this->sut->processToValid();
    }

    /**
     * Test no updates done if status isn't "valid"
     */
    public function testToValidInValidStatuses()
    {
        $mockLicenceStatusRuleService = m::mock('\StdClass');
        $this->sm->setService('Entity\LicenceStatusRule', $mockLicenceStatusRuleService);

        $mockVehicleService = m::mock('\StdClass');
        $this->sm->setService('Entity\Vehicle', $mockVehicleService);

        $mockLicenceService = m::mock('\StdClass');
        $this->sm->setService('Entity\Licence', $mockLicenceService);

        $mockDateService = m::mock('\StdClass');
        $this->sm->setService('Helper\Date', $mockDateService);
        $mockDateService->shouldReceive('getDate')->andReturn('2015-03-24');

        $getLicencesToValid = [
            [
                'id' => 65765,
                'licence' => [
                    'id' => 1221,
                    'status' => [
                        'id' => LicenceEntityService::LICENCE_STATUS_VALID,
                        'description' => 'Foobar',
                    ]
                ]
            ],
            [
                'id' => 65765,
                'licence' => [
                    'id' => 1221,
                    'status' => [
                        'id' => LicenceEntityService::LICENCE_STATUS_GRANTED,
                        'description' => 'Foobar',
                    ]
                ]
            ],
            [
                'id' => 65765,
                'licence' => [
                    'id' => 1221,
                    'status' => [
                        'id' => LicenceEntityService::LICENCE_STATUS_WITHDRAWN,
                        'description' => 'Foobar',
                    ]
                ]
            ],
        ];

        $mockLicenceStatusRuleService->shouldReceive('getLicencesToValid')
            ->andReturn($getLicencesToValid);

        $mockLicenceService->shouldReceive('forceUpdate')->never();
        $mockLicenceStatusRuleService->shouldReceive('forceUpdate')->never();

        $this->sut->processToValid();
    }
}
