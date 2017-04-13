<?php

/**
 * Licence Overview Helper Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Service\Helper;

use Olcs\Service\Helper\LicenceOverviewHelperService;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Common\RefData;
use Mockery as m;

/**
 * Licence Overview Helper Service Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class LicenceOverviewHelperServiceTest extends MockeryTestCase
{
    /**
     * @dataProvider getViewDataProvider
     * @param array $licenceData licence overview data
     * @param array $expectedViewData
     */
    public function testGetViewData($licenceData, $expectedViewData)
    {
        $this->markTestSkipped('2.4 upgrade and servicelocator changes for tests');

        // Overwrite the Helper Url service
        $serviceManager = Bootstrap::getServiceManager();

        // Place the service locator into the Helper Service to test
        $helperService = new LicenceOverviewHelperService();
        $helperService->setServiceLocator($serviceManager);

        $this->assertEquals($expectedViewData, $helperService->getViewData($licenceData));
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
                    'licenceType'  => ['id' => RefData::LICENCE_TYPE_STANDARD_NATIONAL],
                    'status'       => ['id' => RefData::LICENCE_STATUS_VALID],
                    'goodsOrPsv'   => ['id' => RefData::LICENCE_CATEGORY_GOODS_VEHICLE],
                    'totAuthVehicles' => 10,
                    'totAuthTrailers' => 8,
                    'totCommunityLicences' => null,
                    'organisation' => [
                        'allowEmail' => 'Y',
                        'id' => 72,
                        'name' => 'John Smith Haulage',
                        'leadTcArea' => ['id' => 'B'],
                        'organisationUsers' => [
                            ['isAdministrator' => 'Y']
                        ]
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
                    'tradingName' => 'JSH Logistics',
                    'complaintsCount' => 0,
                    'gracePeriods' => [],
                    'currentApplications' => [
                        ['id' => 91],
                        ['id' => 92],
                        ['id' => 93],
                        ['id' => 94],
                    ],
                    'openCases' =>  [
                        ['id' => 2], ['id' => 3], ['id' => 4]
                    ],
                    'busCount' => '4',
                    'organisationLicenceCount' => 3,
                    'numberOfVehicles' => 5
                ],
                // expected view data
                [
                    'operatorName'               => 'John Smith Haulage',
                    'operatorId'                 => 72,
                    'numberOfLicences'           => 3,
                    'tradingName'                => 'JSH Logistics',
                    'currentApplications'        => '4 (<a href="APP_SEARCH_URL">view</a>)',
                    'licenceNumber'              => 'OB1234567',
                    'licenceStartDate'           => '2014-03-02',
                    'licenceType'                => RefData::LICENCE_TYPE_STANDARD_NATIONAL,
                    'licenceStatus'              => ['id' => RefData::LICENCE_STATUS_VALID],
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
                    'registeredForSelfService'   => 'Yes',
                    'previousOperatorName'       => 'TEST',
                    'previousLicenceNumber'      => 'TEST',
                    'isPsv'                      => false,
                    'licenceGracePeriods'        => 'None (<a href="GRACE_PERIOD_URL">manage</a>)',
                    'numberOfBusRegistrations'   => '4',

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
                    'licenceType'  => ['id' => RefData::LICENCE_TYPE_RESTRICTED],
                    'status'       => ['id' => RefData::LICENCE_STATUS_SURRENDERED],
                    'goodsOrPsv'   => ['id' => RefData::LICENCE_CATEGORY_PSV],
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
                        'leadTcArea' => ['id' => 'B'],
                        'organisationUsers' => [
                            ['isAdministrator' => 'N']
                        ]
                    ],
                    'operatingCentres' => [
                        ['id' => 1],
                        ['id' => 2],
                    ],
                    'openCases' => [
                        ['id' => 2, 'publicInquiry' => []],
                        ['id' => 3, 'publicInquiry' => []],
                        ['id' => 4, 'publicInquiry' => ['id' => 99]],
                    ],
                    'gracePeriods' => [
                        [
                            'id' => 1,
                            'isActive' => true,
                        ],
                    ],
                    'currentApplications' => [
                        ['id' => 91],
                        ['id' => 92],
                    ],
                    'complaintsCount' => 0,
                    'tradingName' => 'None',
                    'busCount' => '4',
                    'organisationLicenceCount' => 3,
                    'numberOfVehicles' => 5
                ],
                // expectedViewData
                [
                    'operatorName'               => 'John Smith Coaches',
                    'operatorId'                 => 72,
                    'numberOfLicences'           => 3,
                    'tradingName'                => 'None',
                    'currentApplications'        => '2 (<a href="APP_SEARCH_URL">view</a>)',
                    'licenceNumber'              => 'PD2737280',
                    'licenceStartDate'           => '2014-03-02',
                    'licenceType'                => RefData::LICENCE_TYPE_RESTRICTED,
                    'licenceStatus'              => ['id' => RefData::LICENCE_STATUS_SURRENDERED],
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
                    'registeredForSelfService'   => 'No',
                    'isPsv'                      => true,
                    'licenceGracePeriods'        => 'Active (<a href="GRACE_PERIOD_URL">manage</a>)',
                    'numberOfBusRegistrations'   => '4',
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
                        'allowEmail' => 'Y',
                        'id' => 72,
                        'name' => 'John Smith Taxis',
                        'leadTcArea' => ['id' => 'B'],
                        'organisationUsers' => []
                    ],
                    'operatingCentres' => [],
                    'tradingName' => 'JSH R Us',
                    'complaintsCount' => 0,
                    'openCases' =>[
                        ['id' => 2, 'publicInquiry' => null],
                        ['id' => 3, 'publicInquiry' => null],
                        ['id' => 4, 'publicInquiry' => ['id' => 99]],
                    ],
                    'gracePeriods' => [],
                    'currentApplications' => [],
                    'busCount' => '4',
                    'organisationLicenceCount' => 1,
                    'numberOfVehicles' => 2
                ],
                // expectedViewData
                [
                    'operatorName'               => 'John Smith Taxis',
                    'operatorId'                 => 72,
                    'numberOfLicences'           => 1,
                    'tradingName'                => 'JSH R Us',
                    'currentApplications'        => 0,
                    'licenceNumber'              => 'PD2737280',
                    'licenceStartDate'           => '2014-03-02',
                    'licenceType'                => RefData::LICENCE_TYPE_SPECIAL_RESTRICTED,
                    'licenceStatus'              => ['id' => RefData::LICENCE_STATUS_VALID],
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
                    'registeredForSelfService'   => 'No',
                    'isPsv'                      => true,
                    'licenceGracePeriods'        => 'None (<a href="GRACE_PERIOD_URL">manage</a>)',
                    'numberOfBusRegistrations'   => '4',
                ],
            ],
        ];
    }
}
