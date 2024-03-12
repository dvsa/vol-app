<?php

/**
 * Application Overview Helper Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Service\Helper;

use Common\RefData;
use Common\Service\Helper\UrlHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Helper\ApplicationOverviewHelperService;
use Olcs\Service\Helper\LicenceOverviewHelperService;

/**
 * Application Overview Helper Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ApplicationOverviewHelperServiceTest extends MockeryTestCase
{
    protected $sut;

    /** @var LicenceOverviewHelperService */
    protected $licenceOverviewHelperService;

    /** @var UrlHelperService */
    protected $urlHelperService;

    public function setUp(): void
    {
        parent::setUp();

        $this->licenceOverviewHelperService = m::mock(LicenceOverviewHelperService::class);
        $this->urlHelperService = m::mock(UrlHelperService::class);

        $this->sut = new ApplicationOverviewHelperService(
            $this->licenceOverviewHelperService,
            $this->urlHelperService
        );
    }

    /**
     * @dataProvider getViewDataProvider
     * @param array $overviewData overview data
     * @param array $expectedViewData
     */
    public function testGetViewData($overviewData, $expectedViewData, $gracePeriodStr)
    {
        $lva = 'application';

        // mocks
        $this->licenceOverviewHelperService
            ->shouldReceive('hasAdminUsers')
            ->with($overviewData['licence'])
            ->andReturn(true)
            ->once();

        // expectations
        $this->licenceOverviewHelperService
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
            ->andReturn($gracePeriodStr);

        $this->urlHelperService
            ->shouldReceive('fromRoute')
            ->with('lva-'.$lva.'/interim', [], [], true)
            ->andReturn('INTERIM_URL')
            ->shouldReceive('fromRoute')
            ->with('lva-application/change-of-entity', ['application' => 69])
            ->andReturn('CHANGE_OF_ENTITY_URL')
            ->shouldReceive('fromRoute')
            ->with('licence/grace-periods', ['licence' => 123])
            ->andReturn('GRACE_PERIOD_URL');

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
                    'goodsOrPsv' => ['id' => RefData::LICENCE_CATEGORY_GOODS_VEHICLE],
                    'licenceType'  => ['id' => RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL],
                    'applicableAuthProperties' => [
                        'totAuthVehicles',
                        'totAuthTrailers',
                    ],
                    'totAuthVehicles' => 12,
                    'totAuthTrailers' => 13,
                    'isVariation' => false,
                    'interimStatus' => [
                        'id' => 1,
                        'description' => 'Requested',
                    ],
                    'licenceVehicles' => [
                        ['id' => 1],
                        ['id' => 2],
                    ],
                    'licence' => [
                        'gracePeriods' => [],
                        'id'           => 123,
                        'expiryDate'   => '2017-06-05',
                        'inForceDate'  => '2014-03-02',
                        'status'       => ['id' => RefData::LICENCE_STATUS_VALID],
                        'licenceType'  => ['id' => RefData::LICENCE_TYPE_STANDARD_NATIONAL],
                        'totAuthVehicles' => null,
                        'totAuthTrailers' => null,
                        // 'totCommunityLicences' => null,
                        'organisation' => [
                            'allowEmail' => 'Y',
                            'id' => 72,
                            'name' => 'John Smith Haulage',
                            'licences' => null,
                        ],
                        'tradingName' => 'TRADING_NAME',
                        'licenceVehicles' => null,
                        'operatingCentres' => [
                            ['id' => 1],
                            ['id' => 2],
                        ],
                        'changeOfEntitys' => [],
                        'organisationLicenceCount' => 3,
                        'numberOfVehicles' => 5
                    ],
                    'oppositionCount' => 2,
                    'feeCount' => 2,
                    'outOfOppositionDate' => '1966-06-21',
                    'outOfRepresentationDate' => '1996-07-02',
                    'operatingCentresNetDelta' => 1,
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
                    'licenceStatus' => ['id' => RefData::LICENCE_STATUS_VALID],
                    'licenceType' => RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                    'appLicenceType' => RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    'interimStatus' => 'Requested (<a class="govuk-link" href="INTERIM_URL">Interim details</a>)',
                    'outstandingFees' => 2,
                    'licenceStartDate' => '2014-03-02',
                    'continuationDate' => '2017-06-05',
                    'numberOfVehicles' => '5 (7)',
                    'totalVehicleAuthorisation' => '0 (12)',
                    'numberOfOperatingCentres' => '2 (3)',
                    'totalTrailerAuthorisation' => '0 (13)',
                    'numberOfIssuedDiscs' => null,
                    'numberOfCommunityLicences' => 101,
                    'openCases' => 102,

                    'currentReviewComplaints' => null,
                    'previousOperatorName' => null,
                    'previousLicenceNumber' => null,

                    'outOfOpposition' => null,
                    'outOfRepresentation' => null,
                    'changeOfEntity' => 'No (<a class="govuk-link js-modal-ajax" href="CHANGE_OF_ENTITY_URL">add details</a>)',
                    'receivesMailElectronically' => 'Y',
                    'registeredForSelfService' => 'Yes',
                    'licenceGracePeriods' => 'None (<a class="govuk-link" href="GRACE_PERIOD_URL">manage</a>)',
                    'outOfOpposition' => '1966-06-21',
                    'outOfRepresentation' => '1996-07-02',
                    'isPsv' => false,
                ],
                // grace period string
                'None (<a class="govuk-link" href="GRACE_PERIOD_URL">manage</a>)'
            ],
            'variation eligible for lgv' => [
                // application overview data
                [
                    'id' => 69,
                    'createdOn' => '2015-04-08',
                    'goodsOrPsv' => ['id' => RefData::LICENCE_CATEGORY_GOODS_VEHICLE],
                    'licenceType'  => ['id' => RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL],
                    'applicableAuthProperties' => [
                        'totAuthHgvVehicles',
                        'totAuthLgvVehicles',
                        'totAuthTrailers',
                    ],
                    'totAuthVehicles' => 10,
                    'totAuthHgvVehicles' => 4,
                    'totAuthLgvVehicles' => 6,
                    'totAuthTrailers' => 13,
                    'isVariation' => false,
                    'interimStatus' => [
                        'id' => 1,
                        'description' => 'Requested',
                    ],
                    'licenceVehicles' => [
                        ['id' => 1],
                        ['id' => 2],
                    ],
                    'licence' => [
                        'gracePeriods' => [],
                        'id'           => 123,
                        'expiryDate'   => '2017-06-05',
                        'inForceDate'  => '2014-03-02',
                        'status'       => ['id' => RefData::LICENCE_STATUS_VALID],
                        'licenceType'  => ['id' => RefData::LICENCE_TYPE_STANDARD_NATIONAL],
                        'totAuthHgvVehicles' => 3,
                        'totAuthLgvVehicles' => 2,
                        'totAuthTrailers' => 1,
                        'organisation' => [
                            'allowEmail' => 'Y',
                            'id' => 72,
                            'name' => 'John Smith Haulage',
                            'licences' => null,
                        ],
                        'tradingName' => 'TRADING_NAME',
                        'licenceVehicles' => null,
                        'operatingCentres' => [
                            ['id' => 1],
                            ['id' => 2],
                        ],
                        'changeOfEntitys' => [],
                        'organisationLicenceCount' => 3,
                        'numberOfVehicles' => 5
                    ],
                    'oppositionCount' => 2,
                    'feeCount' => 2,
                    'outOfOppositionDate' => '1966-06-21',
                    'outOfRepresentationDate' => '1996-07-02',
                    'operatingCentresNetDelta' => 1,
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
                    'licenceStatus' => ['id' => RefData::LICENCE_STATUS_VALID],
                    'licenceType' => RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                    'appLicenceType' => RefData::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                    'interimStatus' => 'Requested (<a class="govuk-link" href="INTERIM_URL">Interim details</a>)',
                    'outstandingFees' => 2,
                    'licenceStartDate' => '2014-03-02',
                    'continuationDate' => '2017-06-05',
                    'numberOfVehicles' => '5 (7)',
                    'totalHgvAuthorisation' => '3 (4)',
                    'totalLgvAuthorisation' => '2 (6)',
                    'numberOfOperatingCentres' => '2 (3)',
                    'totalTrailerAuthorisation' => '1 (13)',
                    'numberOfIssuedDiscs' => null,
                    'numberOfCommunityLicences' => 101,
                    'openCases' => 102,
                    'currentReviewComplaints' => null,
                    'previousOperatorName' => null,
                    'previousLicenceNumber' => null,
                    'outOfOpposition' => null,
                    'outOfRepresentation' => null,
                    'changeOfEntity' => 'No (<a class="govuk-link js-modal-ajax" href="CHANGE_OF_ENTITY_URL">add details</a>)',
                    'receivesMailElectronically' => 'Y',
                    'registeredForSelfService' => 'Yes',
                    'licenceGracePeriods' => 'None (<a class="govuk-link" href="GRACE_PERIOD_URL">manage</a>)',
                    'outOfOpposition' => '1966-06-21',
                    'outOfRepresentation' => '1996-07-02',
                    'isPsv' => false,
                ],
                // grace period string
                'None (<a class="govuk-link" href="GRACE_PERIOD_URL">manage</a>)'
            ],
            'new psv special restricted application' => [
                // application overview data
                [
                    'id' => 69,
                    'createdOn' => '2015-04-08',
                    'goodsOrPsv' => ['id' => RefData::LICENCE_CATEGORY_PSV],
                    'licenceType'  => ['id' => RefData::LICENCE_TYPE_SPECIAL_RESTRICTED],
                    'applicableAuthProperties' => [
                    ],
                    'totAuthVehicles' => 5,
                    'isVariation' => false,
                    'licenceVehicles' => [],
                    'licence' => [
                        'gracePeriods' => [
                            [
                                'id' => '99',
                                'isActive' => true
                            ],
                        ],
                        'id'           => 123,
                        'expiryDate'   => '2017-06-05',
                        'inForceDate'  => '2014-03-02',
                        'surrenderedDate' => '2015-02-11',
                        'licenceType'  => ['id' => RefData::LICENCE_TYPE_SPECIAL_RESTRICTED],
                        'status'       => ['id' => RefData::LICENCE_STATUS_VALID],
                        'goodsOrPsv'   => ['id' => RefData::LICENCE_CATEGORY_PSV],
                        'totAuthVehicles' => 2,
                        'totAuthTrailers' => 0,
                        'totCommunityLicences' => 0,
                        'psvDiscs' => [
                            ['id' => 69],
                            ['id' => 70],
                        ],
                        'organisation' => [
                            'allowEmail' => 'N',
                            'id' => 72,
                            'name' => 'John Smith Taxis',
                            'licences' => null,
                        ],
                        'tradingName' => 'TRADING_NAME',
                        'licenceVehicles' => [
                            ['id' => 1],
                            ['id' => 2],
                        ],
                        'operatingCentres' => [],
                        'changeOfEntitys' => [],
                        'organisationLicenceCount' => 1
                    ],
                    'oppositionCount' => 2,
                    'feeCount' => 2,
                    'outOfOppositionDate' => 'Not applicable',
                    'outOfRepresentationDate' => 'Not applicable',
                    'operatingCentresNetDelta' => 1,
                ],
                // expected view data
                [
                    'operatorName' => 'John Smith Taxis',
                    'operatorId' => 72,
                    'numberOfLicences' => 1,
                    'tradingName' => 'TRADING_NAME',
                    'currentApplications' => 100,
                    'applicationCreated' => '2015-04-08',
                    'oppositionCount' => 2,
                    'licenceStatus' => ['id' => RefData::LICENCE_STATUS_VALID],
                    'licenceType' => RefData::LICENCE_TYPE_SPECIAL_RESTRICTED,
                    'appLicenceType' => RefData::LICENCE_TYPE_SPECIAL_RESTRICTED,
                    'interimStatus' => null,
                    'outstandingFees' => 2,
                    'licenceStartDate' => '2014-03-02',
                    'continuationDate' => '2017-06-05',
                    'numberOfVehicles' => null,          // should be null for Special Restricted
                    'numberOfOperatingCentres' => null,  // should be null for Special Restricted
                    'numberOfIssuedDiscs' => null,
                    'numberOfCommunityLicences' => 101,
                    'openCases' => 102,

                    'currentReviewComplaints' => null,
                    'previousOperatorName' => null,
                    'previousLicenceNumber' => null,

                    'outOfOpposition' => null,
                    'outOfRepresentation' => null,
                    'changeOfEntity' => 'No (<a class="govuk-link js-modal-ajax" href="CHANGE_OF_ENTITY_URL">add details</a>)',
                    'receivesMailElectronically' => 'N',
                    'registeredForSelfService' => 'Yes',
                    'licenceGracePeriods' => 'Active (<a class="govuk-link" href="GRACE_PERIOD_URL">manage</a>)',
                    'outOfOpposition' => 'Not applicable',
                    'outOfRepresentation' => 'Not applicable',
                    'isPsv' => true,
                ],
                // grace period str
                'Active (<a class="govuk-link" href="GRACE_PERIOD_URL">manage</a>)',
            ],
        ];
    }

    /**
     * @dataProvider getInterimStatusProvider
     * @param array $applicationData
     * @param array $expected
     */
    public function testGetInterimStatus($applicationData, $expected)
    {
        $this->urlHelperService
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
                    'goodsOrPsv' => ['id' => RefData::LICENCE_CATEGORY_GOODS_VEHICLE],
                ],
                'Requested (<a class="govuk-link" href="INTERIM_URL">Interim details</a>)'
            ],
            'no interim' => [
                null,
                'None (<a class="govuk-link" href="INTERIM_URL">add interim</a>)',
            ],
        ];
    }

    /**
     * @dataProvider getEntityChangeProvider
     */
    public function testGetChangeOfEntity($application, $expected)
    {
        $this->urlHelperService
            ->shouldReceive('fromRoute')
            ->with(
                'lva-application/change-of-entity',
                [
                    'application' => $application['id'],
                    'changeId' => $application['licence']['changeOfEntitys'][0]['id']
                ]
            )
            ->andReturn('CHANGE_OF_ENTITY_URL');

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
                'Yes (<a class="govuk-link js-modal-ajax" href="CHANGE_OF_ENTITY_URL">update details</a>)'
            ],
        ];
    }
}
