<?php

/**
 * Licence Overview Helper Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Service\Helper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Helper\LicenceOverviewHelperService as Sut;
use OlcsTest\Bootstrap;
use Common\Service\Entity\LicenceEntityService as Licence;
use Common\Service\Entity\ApplicationEntityService as Application;

/**
 * Licence Overview Helper Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class LicenceOverviewHelperServiceTest extends MockeryTestCase
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
     * @param array $licenceData licence overview data
     * @param array $cases
     * @param array $applications organisation applications
     * @param array $expectedViewData
     */
    public function testGetViewData($licenceData, $cases, $applications, $expectedViewData)
    {
        $this->sm->shouldReceive('get')->with('Entity\Cases')->andReturn(
            m::mock()
                ->shouldReceive('getOpenForLicence')
                    ->with($licenceData['id'])
                    ->andReturn($cases)
                ->shouldReceive('getOpenComplaintsForLicence')
                    ->with($licenceData['id'])
                    ->andReturn(
                        array(
                            'complaints' => 1
                        )
                    )
                ->getMock()
        );

        $this->sm->shouldReceive('get')->with('Entity\Organisation')->andReturn(
            m::mock()
                ->shouldReceive('getAllApplicationsByStatus')
                    ->with(
                        $licenceData['organisation']['id'],
                        [Application::APPLICATION_STATUS_UNDER_CONSIDERATION, Application::APPLICATION_STATUS_GRANTED]
                    )
                    ->andReturn($applications)
                ->getMock()
        );

        $this->assertEquals($expectedViewData, $this->sut->getViewData($licenceData));
    }

    public function getViewDataProvider()
    {
        return [
            'valid goods licence' => [
                // licence overview data
                [
                    'id'           => 123,
                    'licNo'        => 'OB1234567',
                    'version'      => 1,
                    'reviewDate'   => '2016-05-04',
                    'expiryDate'   => '2017-06-05',
                    'inForceDate'  => '2014-03-02',
                    'licenceType'  => ['id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL],
                    'status'       => ['id' => Licence::LICENCE_STATUS_VALID],
                    'goodsOrPsv'   => ['id' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE],
                    'totAuthVehicles' => 10,
                    'totAuthTrailers' => 8,
                    'totCommunityLicences' => null,
                    'organisation' => [
                        'allowEmail' => 'Y',
                        'id' => 72,
                        'name' => 'John Smith Haulage',
                        'tradingNames' => [
                            [
                                'name' => 'JSH R Us',
                                'createdOn' => '2015-02-18T15:13:15+0000'
                            ],
                            [
                                'name' => 'JSH Logistics',
                                'createdOn' => '2014-02-18T15:13:15+0000'
                            ],
                        ],
                        'licences' => [
                            ['id' => 210],
                            ['id' => 208],
                            ['id' => 203],
                        ],
                        'leadTcArea' => ['id' => 'B'],
                    ],
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
                    'changeOfEntitys' => [
                        [
                            'oldOrganisationName' => "TEST",
                            'oldLicenceNo' => "TEST"
                        ]
                    ],
                ],
                // cases
                [
                    ['id' => 2], ['id' => 3], ['id' => 4]
                ],
                // applications
                [
                    ['id' => 91],
                    ['id' => 92],
                    ['id' => 93],
                    ['id' => 94],
                ],
                // expected view data
                [
                    'operatorName'               => 'John Smith Haulage',
                    'operatorId'                 => 72,
                    'numberOfLicences'           => 3,
                    'tradingName'                => 'JSH Logistics',
                    'currentApplications'        => 4,
                    'licenceNumber'              => 'OB1234567',
                    'licenceStartDate'           => '2014-03-02',
                    'licenceType'                => Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                    'licenceStatus'              => Licence::LICENCE_STATUS_VALID,
                    'surrenderedDate'            => null,
                    'numberOfVehicles'           => 5,
                    'totalVehicleAuthorisation'  => 10,
                    'numberOfOperatingCentres'   => 2,
                    'totalTrailerAuthorisation'  => 8,    // goods only
                    'numberOfIssuedDiscs'        => null, // psv only
                    'numberOfCommunityLicences'  => null,
                    'openCases'                  => '3',
                    'currentReviewComplaints'    => 0,
                    'receivesMailElectronically' => 'Y',
                    'registeredForSelfService'   => null,
                    'previousOperatorName'       => 'TEST',
                    'previousLicenceNumber'      => 'TEST',
                    'isPsv'                      => false,
                ],
            ],
            'surrendered psv licence' => [
                // overviewData
                [
                    'id'           => 123,
                    'licNo'        => 'PD2737280',
                    'version'      => 1,
                    'reviewDate'   => '2016-05-04',
                    'expiryDate'   => '2017-06-05',
                    'inForceDate'  => '2014-03-02',
                    'surrenderedDate' => '2015-02-11',
                    'licenceType'  => ['id' => Licence::LICENCE_TYPE_RESTRICTED],
                    'status'       => ['id' => Licence::LICENCE_STATUS_SURRENDERED],
                    'goodsOrPsv'   => ['id' => Licence::LICENCE_CATEGORY_PSV],
                    'totAuthVehicles' => 10,
                    'totAuthTrailers' => 0,
                    'totCommunityLicences' => 7,
                    'psvDiscs' => [
                        ['id' => 69],
                        ['id' => 70],
                        ['id' => 71],
                        ['id' => 72],
                        ['id' => 73],
                        ['id' => 74],
                    ],
                    'organisation' => [
                        'allowEmail' => 'N',
                        'id' => 72,
                        'name' => 'John Smith Coaches',
                        'tradingNames' => [],
                        'licences' => [
                            ['id' => 210],
                            ['id' => 208],
                            ['id' => 203],
                        ],
                        'leadTcArea' => ['id' => 'B'],
                    ],
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
                ],
                // cases
                [
                    ['id' => 2, 'publicInquirys' => []],
                    ['id' => 3, 'publicInquirys' => []],
                    ['id' => 4, 'publicInquirys' => [ 'id' => 99]],
                ],
                // applications
                [
                    ['id' => 91],
                    ['id' => 92],
                ],
                // expectedViewData
                [
                    'operatorName'               => 'John Smith Coaches',
                    'operatorId'                 => 72,
                    'numberOfLicences'           => 3,
                    'tradingName'                => 'None',
                    'currentApplications'        => 2,
                    'licenceNumber'              => 'PD2737280',
                    'licenceStartDate'           => '2014-03-02',
                    'licenceType'                => Licence::LICENCE_TYPE_RESTRICTED,
                    'licenceStatus'              => Licence::LICENCE_STATUS_SURRENDERED,
                    'surrenderedDate'            => '2015-02-11',
                    'numberOfVehicles'           => 5,
                    'totalVehicleAuthorisation'  => 10,
                    'numberOfOperatingCentres'   => 2,
                    'totalTrailerAuthorisation'  => null, // goods only
                    'numberOfIssuedDiscs'        => 6,    // psv only
                    'numberOfCommunityLicences'  => 7,
                    'openCases'                  => '3 (PI)',
                    'currentReviewComplaints'    => 0,
                    'previousOperatorName'       => null,
                    'previousLicenceNumber'      => null,
                    'receivesMailElectronically' => 'N',
                    'registeredForSelfService'   => null,
                    'isPsv'                      => true,
                ],
            ],
            'special restricted psv licence' => [
                // overviewData
                [
                    'id'           => 123,
                    'licNo'        => 'PD2737280',
                    'version'      => 1,
                    'reviewDate'   => '2016-05-04',
                    'expiryDate'   => '2017-06-05',
                    'inForceDate'  => '2014-03-02',
                    'surrenderedDate' => '2015-02-11',
                    'licenceType'  => ['id' => Licence::LICENCE_TYPE_SPECIAL_RESTRICTED],
                    'status'       => ['id' => Licence::LICENCE_STATUS_VALID],
                    'goodsOrPsv'   => ['id' => Licence::LICENCE_CATEGORY_PSV],
                    'totAuthVehicles' => 2,
                    'totAuthTrailers' => 0,
                    'totCommunityLicences' => 0,
                    'psvDiscs' => [
                        ['id' => 69],
                        ['id' => 70],
                    ],
                    'organisation' => [
                        'allowEmail' => 'Y',
                        'id' => 72,
                        'name' => 'John Smith Taxis',
                        'tradingNames' => [
                            [
                                'name' => 'JSH R Us',
                                'createdOn' => '2015-02-18T15:13:15+0000'
                            ],
                            [
                                'name' => 'JSH XPress',
                                'createdOn' => '2015-02-18T15:13:15+0000'
                            ],
                        ],
                        'licences' => [
                            ['id' => 210],
                        ],
                        'leadTcArea' => ['id' => 'B'],
                    ],
                    'licenceVehicles' => [
                        ['id' => 1],
                        ['id' => 2],
                    ],
                    'operatingCentres' => [],
                ],
                // cases
                [
                    ['id' => 2, 'publicInquirys' => []],
                    ['id' => 3, 'publicInquirys' => []],
                    ['id' => 4, 'publicInquirys' => [ 'id' => 99]],
                ],
                // applications
                [],
                // expectedViewData
                [
                    'operatorName'               => 'John Smith Taxis',
                    'operatorId'                 => 72,
                    'numberOfLicences'           => 1,
                    'tradingName'                => 'JSH R Us',
                    'currentApplications'        => 0,
                    'licenceNumber'              => 'PD2737280',
                    'licenceStartDate'           => '2014-03-02',
                    'licenceType'                => Licence::LICENCE_TYPE_SPECIAL_RESTRICTED,
                    'licenceStatus'              => Licence::LICENCE_STATUS_VALID,
                    'surrenderedDate'            => null,
                    'numberOfVehicles'           => null,
                    'totalVehicleAuthorisation'  => null,
                    'numberOfOperatingCentres'   => null,
                    'totalTrailerAuthorisation'  => null,
                    'numberOfIssuedDiscs'        => null,
                    'numberOfCommunityLicences'  => 0,
                    'openCases'                  => '3 (PI)',
                    'currentReviewComplaints'    => 0,
                    'previousOperatorName'       => null,
                    'previousLicenceNumber'      => null,
                    'receivesMailElectronically' => 'Y',
                    'registeredForSelfService'   => null,
                    'isPsv'                      => true,
                ],
            ],
        ];
    }
}
