<?php

/**
 * Application Overview Helper Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Service\Helper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Helper\ApplicationOverviewHelperService as Sut;
use OlcsTest\Bootstrap;
use Common\Service\Entity\LicenceEntityService as Licence;
use Common\Service\Entity\ApplicationEntityService as Application;

/**
 * Application Overview Helper Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ApplicationOverviewHelperServiceTest extends MockeryTestCase
{
    protected $sut;

    protected $sm;

    public function setUp()
    {
        parent::setUp();

        $this->sut = new Sut();
        $this->sm = Bootstrap::getServiceManager();
        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * @dataProvider getViewDataProvider
     * @param array $overviewData overview data
     * @param array $expectedViewData
     */
    public function testGetViewData($overviewData, $expectedViewData)
    {
        $lva = 'application';

        // mocks
        $licenceOverviewHelperMock = m::mock();
        $urlHelperMock = m::mock();
        $this->sm->shouldReceive('get')->with('Helper\LicenceOverview')->andReturn($licenceOverviewHelperMock);
        $this->sm->shouldReceive('get')->with('Helper\Url')->andReturn($urlHelperMock);

        // expectations
        $licenceOverviewHelperMock
            ->shouldReceive('getCurrentApplications')
            ->with($overviewData['licence'])
            ->once()
            ->andReturn(100)
            ->shouldReceive('getNumberOfCommunityLicences')
            ->with($overviewData['licence'])
            ->once()
            ->andReturn(101)
            ->shouldReceive('getOpenCases')
            ->with($overviewData['licence'])
            ->once()
            ->andReturn(102)
            ->shouldReceive('getLicenceGracePeriods')
            ->with($overviewData['licence'])
            ->once()
            ->andReturn('None (<a href="GRACE_PERIOD_URL">manage</a>)');

        $urlHelperMock
            ->shouldReceive('fromRoute')
            ->with('lva-'.$lva.'/interim', [], [], true)
            ->andReturn('INTERIM_URL')
            ->shouldReceive('fromRoute')
            ->with(
                'lva-application/change-of-entity',
                array(
                    'application' => $overviewData['id']
                )
            )->andReturn('CHANGE_OF_ENTITY_URL')
            ->shouldReceive('fromRoute')
            ->with(
                'licence/grace-periods',
                array(
                    'licence' => $overviewData['licence']['id'],
                )
            )
            ->andReturn('GRACE_PERIOD_URL')
            ->getMock();

        $this->assertEquals(
            $expectedViewData,
            $this->sut->getViewData($overviewData, $lva)
        );
    }

    public function getViewDataProvider()
    {
        return [
            'new goods application' => [
                // application overview data
                [
                    'id' => 69,
                    'createdOn' => '2015-04-08',
                    'goodsOrPsv' => ['id' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE],
                    'licenceType'  => ['id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL],
                    'totAuthVehicles' => 12,
                    'totAuthTrailers' => 13,
                    'isVariation' => false,
                    'interimStatus' => [
                        'id' => 1,
                        'description' => 'Requested',
                    ],
                    'licence' => [
                        'gracePeriods' => [],
                        'id'           => 123,
                        'expiryDate'   => '2017-06-05',
                        'inForceDate'  => '2014-03-02',
                        'status'       => ['id' => Licence::LICENCE_STATUS_VALID],
                        'totAuthVehicles' => null,
                        'totAuthTrailers' => null,
                        // 'totCommunityLicences' => null,
                        'organisation' => [
                            'allowEmail' => 'Y',
                            'id' => 72,
                            'name' => 'John Smith Haulage',
                            'licences' => [
                                ['id' => 210],
                                ['id' => 208],
                                ['id' => 203],
                            ],
                        ],
                        'tradingName' => 'TRADING_NAME',
                        'licenceVehicles' => [
                            ['id' => 1],
                            ['id' => 2],
                            ['id' => 3],
                            ['id' => 4],
                            ['id' => 5],
                        ],
                        'operatingCentres' => [
                            ['id' => 1],
                            ['id' => 2],
                        ],
                        'changeOfEntitys' => [],
                    ],
                    'oppositionCount' => 2,
                    'feeCount' => 2,
                ],
                // expected view data
                [
                    'operatorName' => 'John Smith Haulage',
                    'operatorId' => 72,
                    'numberOfLicences' => 3,
                    'tradingName' => 'TRADING_NAME',
                    'currentApplications' => 100,
                    'applicationCreated' => '2015-04-08',
                    'oppositionCount' => 2,
                    'licenceStatus' => Licence::LICENCE_STATUS_VALID,
                    'interimStatus' => 'Requested (<a href="INTERIM_URL">Interim details</a>)',
                    'outstandingFees' => 2,
                    'licenceStartDate' => '2014-03-02',
                    'continuationDate' => '2017-06-05',
                    'numberOfVehicles' => 5,
                    'totalVehicleAuthorisation' => '0 (12)',
                    'numberOfOperatingCentres' => 2,
                    'totalTrailerAuthorisation' => '0 (13)',
                    'numberOfIssuedDiscs' => null,
                    'numberOfCommunityLicences' => 101,
                    'openCases' => 102,

                    'currentReviewComplaints' => null,
                    'previousOperatorName' => null,
                    'previousLicenceNumber' => null,

                    'outOfOpposition' => null,
                    'outOfRepresentation' => null,
                    'changeOfEntity' => 'No (<a class="js-modal-ajax" href="CHANGE_OF_ENTITY_URL">add details</a>)',
                    'receivesMailElectronically' => 'Y',
                    'registeredForSelfService' => null,
                    'licenceGracePeriods' => 'None (<a href="GRACE_PERIOD_URL">manage</a>)'
                ],
            ],
            // 'new psv special restricted application' => [
            //     // application overview data
            //     [
            //         'id' => 69,
            //         'createdOn' => '2015-04-08',
            //         'goodsOrPsv' => ['id' => Licence::LICENCE_CATEGORY_PSV],
            //         'licenceType'  => ['id' => Licence::LICENCE_TYPE_SPECIAL_RESTRICTED],
            //         'totAuthVehicles' => 5,
            //         'isVariation' => false
            //     ],
            //     // Grace periods
            //     [
            //         'Count' => 1,
            //         'Results' => array(
            //             array()
            //         )
            //     ],
            //     // licence overview data
            //     [
            //         'id'           => 123,
            //         'expiryDate'   => '2017-06-05',
            //         'inForceDate'  => '2014-03-02',
            //         'surrenderedDate' => '2015-02-11',
            //         'licenceType'  => ['id' => Licence::LICENCE_TYPE_SPECIAL_RESTRICTED],
            //         'status'       => ['id' => Licence::LICENCE_STATUS_VALID],
            //         'goodsOrPsv'   => ['id' => Licence::LICENCE_CATEGORY_PSV],
            //         'totAuthVehicles' => 2,
            //         'totAuthTrailers' => 0,
            //         'totCommunityLicences' => 0,
            //         'psvDiscs' => [
            //             ['id' => 69],
            //             ['id' => 70],
            //         ],
            //         'organisation' => [
            //             'allowEmail' => 'N',
            //             'id' => 72,
            //             'name' => 'John Smith Taxis',
            //             'licences' => [
            //                 ['id' => 210],
            //             ],
            //         ],
            //         'licenceVehicles' => [
            //             ['id' => 1],
            //             ['id' => 2],
            //         ],
            //         'operatingCentres' => [],
            //     ],
            //     // interim data
            //     [], // n/a on PSV
            //     // expected view data
            //     [
            //         'operatorName' => 'John Smith Taxis',
            //         'operatorId' => 72,
            //         'numberOfLicences' => 1,
            //         'tradingName' => 'TRADING_NAME',
            //         'currentApplications' => 100,
            //         'applicationCreated' => '2015-04-08',
            //         'oppositionCount' => 2,
            //         'licenceStatus' => Licence::LICENCE_STATUS_VALID,
            //         'interimStatus' => null,
            //         'outstandingFees' => 2,
            //         'licenceStartDate' => '2014-03-02',
            //         'continuationDate' => '2017-06-05',
            //         'numberOfVehicles' => null,          // should be null for Special Restricted
            //         'totalVehicleAuthorisation' => null, // should be null for PSV
            //         'numberOfOperatingCentres' => null,  // should be null for Special Restricted
            //         'totalTrailerAuthorisation' => null, // should be null for PSV
            //         'numberOfIssuedDiscs' => null,
            //         'numberOfCommunityLicences' => 101,
            //         'openCases' => 102,

            //         'currentReviewComplaints' => null,
            //         'previousOperatorName' => null,
            //         'previousLicenceNumber' => null,

            //         'outOfOpposition' => null,
            //         'outOfRepresentation' => null,
            //         'changeOfEntity' => 'No (<a class="js-modal-ajax" href="CHANGE_OF_ENTITY_URL">add details</a>)',
            //         'receivesMailElectronically' => 'N',
            //         'registeredForSelfService' => null,
            //         'licenceGracePeriods'        => 'Active (<a href="GRACE_PERIOD_URL">manage</a>)'
            //     ],
            // ],
        ];
    }

    /**
     * @dataProvider getInterimStatusProvider
     * @param array $applicationData
     * @param array $expected
     */
    public function testGetInterimStatus($applicationData, $expected)
    {
        $applicationId = 69;

        $applicationMock = m::mock();
        $urlHelperMock = m::mock();

        $this->sm->shouldReceive('get')->with('Helper\Url')->andReturn($urlHelperMock);

         $urlHelperMock
            ->shouldReceive('fromRoute')
            ->with('lva-application/interim', [], [], true)
            ->andReturn('INTERIM_URL');

        $this->assertEquals($expected, $this->sut->getInterimStatus($applicationData, 'application'));
    }

    public function getInterimStatusProvider()
    {
        return [
            'with interim' => [
                [
                    'interimStatus' => [
                        'id' => 1,
                        'description' => 'Requested',
                    ],
                ],
                'Requested (<a href="INTERIM_URL">Interim details</a>)'
            ],
            'no interim' => [
                null,
                'None (<a href="INTERIM_URL">add interim</a>)',
            ],
        ];
    }

    /**
     * @dataProvider getEntityChangeProvider
     */
    public function testGetChangeOfEntity($application, $expected)
    {
        $urlHelperMock = m::mock()
            ->shouldReceive('fromRoute')
            ->with(
                'lva-application/change-of-entity',
                array(
                    'application' => $application['id'],
                    'changeId' => $application['licence']['changeOfEntitys'][0]['id']
                )
            )
        ->andReturn('CHANGE_OF_ENTITY_URL');

        $this->sm->setService('Helper\Url', $urlHelperMock->getMock());

        $this->assertEquals($expected, $this->sut->getChangeOfEntity($application));
    }

    public function getEntityChangeProvider()
    {
        return [
            'with changes' => [
                [
                    'id' => 69,
                    'licence' => [
                        'id' => 7,
                        'changeOfEntitys' => [
                            [
                                'id' => 1
                            ]
                        ],
                    ],
                ],
                'Yes (<a class="js-modal-ajax" href="CHANGE_OF_ENTITY_URL">update details</a>)'
            ],
        ];
    }
}
