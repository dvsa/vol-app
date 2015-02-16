<?php
namespace OlcsTest\Service\Data;

use Olcs\Service\Data\Submission;

use Mockery as m;

/**
 * Class SubmissionTest
 * @package OlcsTest\Service\Data
 */
class SubmissionDataFilterTest extends \PHPUnit_Framework_TestCase
{
    protected $sut;

    public function setUp()
    {
        $this->sut = new Submission();
    }

    /**
     * Tests creating the individual submission section data.
     * The submissions uses the config bundle to get input from the database, filters it, and returns filtered data
     * The test is to provide mocked input, and the expected result consists of 2 elements. First the loaded data and
     * second is the filtered data.
     *
     * @dataProvider providerSubmissionSections
     * @param $sectionId
     */
    public function testCreateSubmissionSection($sectionId)
    {
        $this->createSubmissionSection(
            $this->getMockSubmissionSectionInput($sectionId),
            $this->getExpectedSectionResults($sectionId)
        );
    }

    /**
     * Actual test
     *
     * @param $input
     * @param $expected
     */
    public function createSubmissionSection($input, $expected)
    {
        $mockRestClient = $this->getMock('\Common\Util\RestClient', [], [], '', false);
        $mockRestClient->expects($this->any())
            ->method('get')
            ->with(
                '',
                array('id' => $input['caseId'],
                    'bundle' => json_encode($input['sectionConfig']['bundle'])
                )
            )
            ->willReturn($expected['loadedCaseSectionData']);

        $mockApiResolver = $this->getMock('stdClass', ['getClient']);
        $mockApiResolver
            ->expects($this->once())
            ->method('getClient')
            ->with($this->equalTo($input['sectionConfig']['service']))
            ->willReturn($mockRestClient);
        $this->sut->setApiResolver($mockApiResolver);

        $wordFilter = new \Zend\Filter\Word\DashToCamelCase();

        $mockFilterManager = $this->getMock('stdClass', ['get']);
        $filterClass = 'Olcs\Filter\SubmissionSection\\' . ucfirst($wordFilter->filter($input['sectionId']));

        $sectionFilter = new $filterClass;

        $sm = $this->getMock(
            'Zend\ServiceManager\ServiceLocatorInterface',
            [
                'getServiceLocator',
                'setServiceLocator',
                'get',
                'has'
            ]
        );
        $dateTimeProcessor = $this->getMock('stdClass', ['calculateDate']);

        $dateTimeProcessor->expects($this->any())
            ->method('calculateDate')
            ->willReturn('25/12/2000');
        $sm->expects($this->any())
            ->method('getServiceLocator')
            ->willReturnSelf();
        $sm->expects($this->any())
            ->method('get')
            ->with('Common\Util\DateTimeProcessor')
            ->willReturn($dateTimeProcessor);

        $sectionFilter->setServiceLocator($sm);

        $mockFilterManager
            ->expects($this->once())
            ->method('get')
            ->with(
                $this->equalTo(
                    'Olcs/Filter/SubmissionSection/' . ucfirst($wordFilter->filter($input['sectionId']))
                )
            )
            ->willReturn($sectionFilter);

        $this->sut->setFilterManager($mockFilterManager);

        $result = $this->sut->createSubmissionSection($input['caseId'], $input['sectionId'], $input['sectionConfig']);

        $this->assertEquals($result, $expected['expected']);
    }

    /**
     * Mocked data input to create each section
     *
     * @param $sectionId
     * @return array
     */
    private function getMockSubmissionSectionInput($sectionId)
    {
        return [
            'caseId' => 24,
            'sectionId' => $sectionId,
            'sectionConfig' => [
                'service' => 'Cases',
                'filter' => true,
                'bundle' => ['some_bundle'],
            ]
        ];
    }

    /**
     * Get mocked expected results. 2 elements:
     * 'loadedCaseSectionData' is data after querying the db
     * 'expected' is the same data after calling the filter
     *
     * @param $sectionId
     * @return array
     */
    private function getExpectedSectionResults($sectionId)
    {
        $wordFilter = new \Zend\Filter\Word\DashToCamelCase();

        $fn = 'provide' . ucfirst($wordFilter->filter($sectionId)) . 'LoadedData';
        $input = $this->$fn();

        $fn = 'provide' . ucfirst($wordFilter->filter($sectionId)) . 'ExpectedResult';
        $expected = $this->$fn();

        return [
            'loadedCaseSectionData' => $input,
            'expected' => $expected
        ];
    }

    public function providerSubmissionSections()
    {
        return [
            //['introduction'],
            ['case-summary'],
            ['case-outline'],
            ['outstanding-applications'],
            ['most-serious-infringement'],
            ['persons'],
            ['operating-centres'],
            //['operating-centre-history'],
            ['conditions-and-undertakings'],
            //['intelligence-unit-check'],
            //['interim'],
            //['advertisement'],
            ['linked-licences-app-numbers'],
            //['all-auths'],
            ['lead-tc-area'],
            //['current-submissions'],
            ['auth-requested-applied-for'],
            ['transport-managers'],
            //['continuous-effective-control'],
            //['fitness-and-repute'],
            //['previous-history'],
            //['bus-reg-app-details'],
            //['transport-authority-comments'],
            //['total-bus-registrations'],
            //['local-licence-history'],
            //['linked-mlh-history'],
            //['registration-details'],
            //['maintenance-tachographs-hours'],
            ['prohibition-history'],
            ['conviction-fpn-offence-history'],
            ['annual-test-history'],
            ['penalties'],
            ['oppositions'],
            ['compliance-complaints'],
            //['other-issues'],
            //['te-reports'],
            //['site-plans'],
            //['planning-permission'],
            //['applicants-comments'],
            //['visibility-access-egress-size'],
            //['case-complaints'],
            ['environmental-complaints'],
            //['representations'],
            //['objections'],
            //['financial-information'],
            //['maps'],
            //['waive-fee-late-fee'],
            //['surrender'],
            //['annex'],
            ['statements']
        ];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideComplianceComplaintsLoadedData()
    {
        return [
            'complaints' => [
                0 => [
                    'id' => 1,
                    'version' => 1,
                    'description' => 'test description 2',
                    'complaintDate' => '2012-06-15T00:00:00+0100',
                    'complainantContactDetails' => [
                        'person' => [
                            'forename' => 'John',
                            'familyName' => 'Smith'
                        ]
                    ]
                ],
                1 => [
                    'id' => 1,
                    'version' => 1,
                    'description' => 'test description 1',
                    'complaintDate' => '2011-06-15T00:00:00+0100',
                    'complainantContactDetails' => [
                        'person' => [
                            'forename' => 'John',
                            'familyName' => 'Smith'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideComplianceComplaintsExpectedResult()
    {
        return [
            'tables' => [
                'compliance-complaints' => [
                    0 => [
                        'id' => 1,
                        'version' => 1,
                        'description' => 'test description 1',
                        'complaintDate' => '2011-06-15T00:00:00+0100',
                        'complainantForename' => 'John',
                        'complainantFamilyName' => 'Smith'
                    ],
                    1 => [
                        'id' => 1,
                        'version' => 1,
                        'description' => 'test description 2',
                        'complaintDate' => '2012-06-15T00:00:00+0100',
                        'complainantForename' => 'John',
                        'complainantFamilyName' => 'Smith'
                    ]
                ]
            ]
        ];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideIntroductionLoadedData()
    {
        return [];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideIntroductionExpectedResult()
    {
        return [];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideCaseSummaryLoadedData()
    {
        return [
            'ecmsNo' => 'E123456',
            'description' => 'Case for convictions against company directors',
            'id' => 24,
            'caseType' =>
                [
                    'id' => 'case_t_lic',
                ],
            'licence' => [
                'licNo' => 'OB1234567',
                'trailersInPossession' => null,
                'totAuthTrailers' => 4,
                'totAuthVehicles' => 12,
                'inForceDate' => '2010-01-12T00:00:00+0000',
                'status' => [
                    'description' => 'New',
                    'id' => 'lsts_consideration',
                ],
                'organisation' => [
                    'isMlh' => 'Y',
                    'name' => 'John Smith Haulage Ltd.',
                    'type' =>
                        [
                            'description' => 'Registered Company',
                            'id' => 'org_t_rc',
                        ],
                    'organisationPersons' => [
                        0 => [
                            'person' => [
                                'id' => 1,
                                'title' => '',
                                'forename' => 'Tom',
                                'familyName' => 'Jones',
                                'birthDate' => '1972-02-15T00:00:00+0100',
                            ],
                        ],
                        1 => [
                            'person' => [
                                'id' => 2,
                                'title' => '',
                                'forename' => 'Keith',
                                'familyName' => 'Winnard',
                                'birthDate' => '1975-03-15T00:00:00+0100',
                            ]
                        ]
                    ],
                    'natureOfBusinesss' => [
                        [
                            'refData' => [
                                'id' => '1',
                                'description' => 'Some whatever'
                            ]
                        ]
                    ]
                ],
                'licenceVehicles' => [
                    0 => [
                        'id' => 1,
                        'deletedDate' => null,
                        'specifiedDate' => '2014-02-20T00:00:00+0000',
                    ],
                    1 => [
                        'id' => 2,
                        'deletedDate' => null,
                        'specifiedDate' => '2014-02-20T00:00:00+0000',
                    ],
                    2 => [
                        'id' => 3,
                        'deletedDate' => null,
                        'specifiedDate' => '2014-02-20T00:00:00+0000',
                    ],
                    3 => [
                        'id' => 4,
                        'deletedDate' => null,
                        'specifiedDate' => '2014-02-20T00:00:00+0000',
                    ],
                ],
                'licenceType' => [
                    'description' => 'Standard National',
                    'id' => 'ltyp_sn',
                ],
                'goodsOrPsv' => [
                    'description' => 'Goods Vehicle',
                ],
            ]
        ];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideCaseSummaryExpectedResult()
    {
        return [
            'overview' => [
                'id' => 24,
                'organisationName' => 'John Smith Haulage Ltd.',
                'isMlh' => 'Y',
                'organisationType' => 'Registered Company',
                'businessType' => 'Some whatever',
                'caseType' => 'case_t_lic',
                'ecmsNo' => 'E123456',
                'licNo' => 'OB1234567',
                'licenceStartDate' => '2010-01-12T00:00:00+0000',
                'licenceType' => 'Standard National',
                'goodsOrPsv' => 'Goods Vehicle',
                'serviceStandardDate' => null,
                'licenceStatus' => 'New',
                'totAuthorisedVehicles' => 12,
                'totAuthorisedTrailers' => 4,
                'vehiclesInPossession' => 4,
                'trailersInPossession' => 4,
            ]
        ];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideCaseOutlineLoadedData()
    {
        return [
            'description' => 'test description',
        ];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideCaseOutlineExpectedResult()
    {
        return [
            'text' => 'test description',
        ];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideOppositionsLoadedData()
    {
        return [
            'oppositions' => [
                0 => [
                    'id' => 1,
                    'version' => 1,
                    'raisedDate' => '2012-03-10T00:00:00+0000',
                    'oppositionType' => [
                        'description' => 'foo'
                    ],
                    'opposer' => [
                        'contactDetails' => [
                            'person' => [
                                'forename' => 'John',
                                'familyName' => 'Smith'
                            ]
                        ]
                    ],
                    'grounds' => [
                        0 => [
                            'grounds' => [
                                'description' => 'bar1'
                            ]
                        ]
                    ],
                    'isValid' => 'Y',
                    'isCopied' => 'Y',
                    'isInTime' => 'Y',
                    'isPublicInquiry' => 'Y',
                    'isWithdrawn' => 'N'
                ],
                1 => [
                    'id' => 2,
                    'version' => 1,
                    'raisedDate' => '2012-02-10T00:00:00+0000',
                    'oppositionType' => [
                        'description' => 'foo'
                    ],
                    'opposer' => [
                        'contactDetails' => [
                            'person' => [
                                'forename' => 'Bob',
                                'familyName' => 'Smith'
                            ]
                        ]
                    ],
                    'grounds' => [
                        0 => [
                            'grounds' => [
                                'description' => 'bar2'
                            ]
                        ]
                    ],
                    'isValid' => 'Y',
                    'isCopied' => 'Y',
                    'isInTime' => 'Y',
                    'isPublicInquiry' => 'Y',
                    'isWithdrawn' => 'N'
                ]
            ]
        ];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideOppositionsExpectedResult()
    {
        return [
            'tables' => [
                'oppositions' => [

                    0 => [
                        'id' => 1,
                        'version' => 1,
                        'dateReceived' => '2012-03-10T00:00:00+0000',
                        'oppositionType' => 'foo',
                        'contactName' => [
                            'forename' => 'John',
                            'familyName' => 'Smith'
                        ],
                        'grounds' => [
                            'bar1'
                        ],
                        'isValid' => 'Y',
                        'isCopied' => 'Y',
                        'isInTime' => 'Y',
                        'isPublicInquiry' => 'Y',
                        'isWithdrawn' => 'N'
                    ],
                    1 => [
                        'id' => 2,
                        'version' => 1,
                        'dateReceived' => '2012-02-10T00:00:00+0000',
                        'oppositionType' => 'foo',
                        'contactName' => [
                            'forename' => 'Bob',
                            'familyName' => 'Smith'
                        ],
                        'grounds' => [
                            'bar2'
                        ],
                        'isValid' => 'Y',
                        'isCopied' => 'Y',
                        'isInTime' => 'Y',
                        'isPublicInquiry' => 'Y',
                        'isWithdrawn' => 'N'
                    ]
                ]
            ]
        ];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideOutstandingApplicationsLoadedData()
    {
        return [
            'id' => 24,
            'version' => 1,
            'licence' => array (
                'id' => 7,
                'version' => 1,
                'organisation' => array(
                    'licences' => array(
                        0 => array(
                            'applications' => array (
                                0 => array (
                                    'id' => 1,
                                    'version' => 1,
                                    'receivedDate' => '2014-03-13',
                                    'operatingCentres' => array (
                                        0 => array (
                                            'adPlacedDate' => '2014-03-13',
                                            'id' => 1,
                                        ),
                                        1 => array (
                                            'adPlacedDate' => '2014-03-21',
                                            'id' => 2,
                                        ),
                                        2 => array (
                                            'adPlacedDate' => '2014-04-01',
                                            'id' => 3,
                                        ),
                                    ),
                                    'publicationLinks' => array (
                                        0 => array (
                                            'publication' => array (
                                                'pubDate' => '2014-10-30',
                                            ),
                                        ),
                                        1 => array (
                                            'publication' => array (
                                                'pubDate' => '2014-10-31',
                                            ),
                                        ),
                                    ),
                                ),
                                1 => array (
                                    'id' => 2,
                                    'version' => 1,
                                    'receivedDate' => '2014-03-13',
                                    'operatingCentres' => array (),
                                    'publicationLinks' => array (
                                        0 => array (
                                            'publication' => array (
                                                'pubDate' => '2014-10-21',
                                            ),
                                        ),
                                    ),
                                ),
                                2 => array (
                                    'id' => 2,
                                    'version' => 1,
                                    'receivedDate' => '2014-03-13',
                                    'operatingCentres' => array (),
                                    'publicationLinks' => array (),
                                ),
                                3 => array (
                                    'id' => 2,
                                    'version' => 1,
                                    'receivedDate' => '2014-03-13',
                                    'operatingCentres' => array (),
                                    'publicationLinks' => array (
                                        0 => array (
                                            'publication' => array (
                                                'pubDate' => '',
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    )
                )
            )
        ];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideOutstandingApplicationsExpectedResult()
    {
        return [
            'tables' => [
                'outstanding-applications' => [
                    0 => [
                        'id' => 1,
                        'version' => 1,
                        'applicationType' => 'TBC',
                        'receivedDate' => '2014-03-13',
                        'oor' => '25/12/2000',
                        'ooo' => '25/12/2000',
                    ],
                    1 => [
                        'id' => 2,
                        'version' => 1,
                        'applicationType' => 'TBC',
                        'receivedDate' => '2014-03-13',
                        'oor' => null,
                        'ooo' => '25/12/2000',
                    ],
                    2 => [
                        'id' => 2,
                        'version' => 1,
                        'applicationType' => 'TBC',
                        'receivedDate' => '2014-03-13',
                        'oor' => null,
                        'ooo' => null,
                    ],
                    3 => [
                        'id' => 2,
                        'version' => 1,
                        'applicationType' => 'TBC',
                        'receivedDate' => '2014-03-13',
                        'oor' => null,
                        'ooo' => null,
                    ]
                ]
            ]
        ];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideMostSeriousInfringementLoadedData()
    {
        return array (
            'id' => 24,
            'version' => 1,
            'seriousInfringements' =>
                array (
                    0 =>
                        array (
                            'checkDate' => '2014-04-04',
                            'infringementDate' => '2014-04-05',
                            'notificationNumber' => '123456',
                            'id' => 2,
                            'memberStateCode' =>
                                array (
                                    'isMemberState' => 'N',
                                ),
                            'siCategory' =>
                                array (
                                    'description' => 'MSI',
                                ),
                            'siCategoryType' =>
                                array (
                                    'description' => 'Exceeding the maximum six-day...',
                                ),
                        ),
                ),
        );
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideMostSeriousInfringementExpectedResult()
    {
        return array (
            'overview' =>
                array (
                    'id' => 2,
                    'notificationNumber' => '123456',
                    'siCategory' => 'MSI',
                    'siCategoryType' =>
                        'Exceeding the maximum six-day...',
                    'infringementDate' => '2014-04-05',
                    'checkDate' => '2014-04-04',
                    'isMemberState' => 'N',
                ),
        );
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function providePersonsLoadedData()
    {
        return [
            'licence' => [
                'organisation' => [
                    'organisationPersons' => [
                        0 => [
                            'person' => [
                                'id' => 2,
                                'title' => 'Mr',
                                'familyName' => 'Smith',
                                'forename' => 'John',
                                'birthDate' => '2012-06-15T00:00:00+0100',
                            ]
                        ],
                        1 => [
                            'person' => [
                                'id' => 1,
                                'title' => 'Mr',
                                'familyName' => 'Smith',
                                'forename' => 'Bob',
                                'birthDate' => '2012-06-15T00:00:00+0100',
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function providePersonsExpectedResult()
    {
        return [
            'tables' => [
                'persons' => [
                    0 => [
                        'id' => 1,
                        'title' => 'Mr',
                        'familyName' => 'Smith',
                        'forename' => 'Bob',
                        'birthDate' => '2012-06-15T00:00:00+0100'
                    ],
                    1 => [
                        'id' => 2,
                        'title' => 'Mr',
                        'familyName' => 'Smith',
                        'forename' => 'John',
                        'birthDate' => '2012-06-15T00:00:00+0100',
                    ]
                ]
            ]
        ];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideOperatingCentresLoadedData()
    {
        return array (
            'licence' =>
                array (
                    'totAuthTrailers' => 99,
                    'licenceVehicles' => [
                        0 => [
                            'id' => 1,
                            'deletedDate' => null,
                            'specifiedDate' => '2014-02-20T00:00:00+0000',
                        ],
                        1 => [
                            'id' => 2,
                            'deletedDate' => null,
                            'specifiedDate' => '2014-02-20T00:00:00+0000',
                        ]
                    ],
                    'operatingCentres' =>
                        array (
                            0 => [
                                'id' => 1,
                                'version' => 1,
                                'noOfVehiclesRequired' => 99,
                                'noOfTrailersRequired' => 77,
                                'operatingCentre' => [
                                    'id' => 16,
                                    'version' => 3,
                                    'address' => [
                                        'addressLine1' => 'Unit 5',
                                        'addressLine2' => '12 Albert Street',
                                        'addressLine3' => 'Westpoint',
                                        'addressLine4' => '',
                                        'paonEnd' => null,
                                        'paonStart' => null,
                                        'postcode' => 'LS9 6NA',
                                        'saonEnd' => null,
                                        'saonStart' => null,
                                        'town' => 'Leeds',
                                        'uprn' => null,
                                        'createdOn' => '2015-01-26T16:34:25+0000',
                                        'id' => 8,
                                        'lastModifiedOn' => '2015-01-26T16:34:25+0000',
                                        'version' => 1,
                                    ],
                                ]
                            ],
                            1 =>
                                array (
                                    'id' => 4,
                                    'version' => 1,
                                    'noOfVehiclesRequired' => 66,
                                    'noOfTrailersRequired' => 33,
                                    'operatingCentre' =>
                                        array (
                                            'id' => 72,
                                            'version' => 4,
                                            'address' =>
                                                array (
                                                    'addressLine1' => '38 George Street',
                                                    'addressLine2' => 'Edgbaston',
                                                    'addressLine3' => '',
                                                    'addressLine4' => '',
                                                    'paonEnd' => null,
                                                    'paonStart' => null,
                                                    'postcode' => 'B15 1PL',
                                                    'saonEnd' => null,
                                                    'saonStart' => null,
                                                    'town' => 'Birmingham',
                                                    'uprn' => null,
                                                    'createdOn' => '2015-01-26T16:34:25+0000',
                                                    'id' => 72,
                                                    'lastModifiedOn' => '2015-01-26T16:34:25+0000',
                                                    'version' => 1,
                                                ),
                                        ),
                                ),
                            2 =>
                                array (
                                    'id' => 5,
                                    'version' => 1,
                                    'noOfVehiclesRequired' => 22,
                                    'noOfTrailersRequired' => 11,
                                    'operatingCentre' =>
                                        array (
                                            'id' => 75,
                                            'version' => 5,
                                        ),
                                ),
                        ),
                )
        );

    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideOperatingCentresExpectedResult()
    {
        return [
            'tables' => [
                'operating-centres' => [
                    0 => [
                        'id' => 75,
                        'version' => 5,
                        'totAuthVehicles' => 22,
                        'totAuthTrailers' => 11,
                        'OcAddress' => [
                        ]
                    ],
                    1 => [
                        'id' => 72,
                        'version' => 4,
                        'totAuthVehicles' => 66,
                        'totAuthTrailers' => 33,
                        'OcAddress' => [
                            'addressLine1' => '38 George Street',
                            'addressLine2' => 'Edgbaston',
                            'addressLine3' => '',
                            'addressLine4' => '',
                            'paonEnd' => null,
                            'paonStart' => null,
                            'postcode' => 'B15 1PL',
                            'saonEnd' => null,
                            'saonStart' => null,
                            'town' => 'Birmingham',
                            'uprn' => null,
                            'createdOn' => '2015-01-26T16:34:25+0000',
                            'id' => 72,
                            'lastModifiedOn' => '2015-01-26T16:34:25+0000',
                            'version' => 1
                        ],
                    ],
                    2 => [
                        'id' => 16,
                        'version' => 3,
                        'totAuthVehicles' => 99,
                        'totAuthTrailers' => 77,
                        'OcAddress' => [
                            'addressLine1' => 'Unit 5',
                            'addressLine2' => '12 Albert Street',
                            'addressLine3' => 'Westpoint',
                            'addressLine4' => '',
                            'paonEnd' => null,
                            'paonStart' => null,
                            'postcode' => 'LS9 6NA',
                            'saonEnd' => null,
                            'saonStart' => null,
                            'town' => 'Leeds',
                            'uprn' => null,
                            'createdOn' => '2015-01-26T16:34:25+0000',
                            'id' => 8,
                            'lastModifiedOn' => '2015-01-26T16:34:25+0000',
                            'version' => 1,
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideOperatingCentreHistoryLoadedData()
    {
        return [];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideOperatingCentreHistoryExpectedResult()
    {
        return [];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideConditionsAndUndertakingsLoadedData()
    {
        return [
            'id' => 24,
            'conditionUndertakings' => [
                0 => [
                    'isDraft' => 'N',
                    'isFulfilled' => 'N',
                    'isApproved' => 'N',
                    'id' => 1,
                    'version' => 1,
                    'createdOn' => '2012-03-10T00:00:00+0000',
                    'attachedTo' => [
                        'description' => 'Operating Centre',
                        'id' => 'cat_oc',
                    ],
                    'conditionType' => [
                        'description' => 'Condition',
                        'id' => 'cdt_con',
                    ],
                    'case' => [
                        'id' => 24,
                    ],
                    'addedVia' => [
                        'description' => 'Case',
                        'id' => 'cav_case',
                    ],
                    'operatingCentre' => [
                        'id' => 16,
                        'address' => [
                            'addressLine2' => '12 Albert Street',
                            'addressLine1' => 'Unit 5',
                            'addressLine3' => 'Westpoint',
                            'addressLine4' => '',
                            'town' => 'Leeds',
                            'postcode' => 'LS9 6NA',
                            'countryCode' => [
                                'id' => 'GB',
                            ],
                        ],
                    ],
                ],
                1 => [
                    'isDraft' => 'N',
                    'isFulfilled' => 'N',
                    'isApproved' => 'N',
                    'id' => 1,
                    'version' => 1,
                    'createdOn' => '2011-03-10T00:00:00+0000',
                    'attachedTo' => [
                        'description' => 'Operating Centre',
                        'id' => 'cat_oc',
                    ],
                    'conditionType' => [
                        'description' => 'Condition',
                        'id' => 'cdt_con',
                    ],
                    'case' => [
                        'id' => 24,
                    ],
                    'addedVia' => [
                        'description' => 'Case',
                        'id' => 'cav_case',
                    ],
                    'operatingCentre' => [
                        // empty address branch test
                    ],
                ]
            ]
        ];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideConditionsAndUndertakingsExpectedResult()
    {
        return [
            'tables' => [
                'conditions' => [
                    0 => [
                        'id' => 1,
                        'version' => 1,
                        'createdOn' => '2012-03-10T00:00:00+0000',
                        'caseId' => 24,
                        'addedVia' => [
                            'description' => 'Case',
                            'id' => 'cav_case',
                        ],
                        'isFulfilled' => 'N',
                        'isDraft' => 'N',
                        'attachedTo' => [
                            'description' => 'Operating Centre',
                            'id' => 'cat_oc',
                        ],
                        'OcAddress' => [
                            'addressLine2' => '12 Albert Street',
                            'addressLine1' => 'Unit 5',
                            'addressLine3' => 'Westpoint',
                            'addressLine4' => '',
                            'town' => 'Leeds',
                            'postcode' => 'LS9 6NA',
                            'countryCode' => [
                                'id' => 'GB',
                            ]
                        ]
                    ],
                    1 => [
                        'id' => 1,
                        'version' => 1,
                        'createdOn' => '2011-03-10T00:00:00+0000',
                        'caseId' => 24,
                        'addedVia' => [
                            'description' => 'Case',
                            'id' => 'cav_case',
                        ],
                        'isFulfilled' => 'N',
                        'isDraft' => 'N',
                        'attachedTo' => [
                            'description' => 'Operating Centre',
                            'id' => 'cat_oc',
                        ],
                        'OcAddress' => []
                    ]
                ],
                'undertakings' => []
            ]
        ];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideIntelligenceUnitCheckLoadedData()
    {
        return [];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideIntelligenceUnitCheckExpectedResult()
    {
        return [];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideInterimLoadedData()
    {
        return [];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideInterimExpectedResult()
    {
        return [];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideAdvertisementLoadedData()
    {
        return [];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideAdvertisementExpectedResult()
    {
        return [];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideLinkedLicencesAppNumbersLoadedData()
    {
        return [
            'id' => 24,
            'licence' => [
                'id' => 24,
                'organisation' => [
                    'licences' => [
                        0 => [
                            'id' => 24,
                            'version' => 1,
                            'licNo' => 'OB1234568',
                            'status' => ['description' => 'Valid'],
                            'licenceType' => ['description' => 'Standard National'],
                            'totAuthTrailers' => '4',
                            'totAuthVehicles' => '5',
                            'licenceVehicles' => [
                                0 => [
                                    'specifiedDate' => '2012-03-10T00:00:00+0000',
                                    'deletedDate' => '2012-03-10T00:00:00+0000'
                                ]
                            ],
                            'createdOn' => '2012-03-10T00:00:00+0000'
                        ],
                        1 => [
                            'id' => 22,
                            'version' => 1,
                            'licNo' => 'OB1234567',
                            'status' => ['description' => 'Curtailed'],
                            'licenceType' => ['description' => 'Standard National'],
                            'totAuthTrailers' => '4',
                            'totAuthVehicles' => '5',
                            'licenceVehicles' => [
                                0 => [
                                    'specifiedDate' => '2012-03-10T00:00:00+0000',
                                    'deletedDate' => '2012-03-10T00:00:00+0000',
                                ]
                            ],
                            'createdOn' => '2012-03-10T00:00:00+0000'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideLinkedLicencesAppNumbersExpectedResult()
    {
        return [
            'tables' => [
                'linked-licences-app-numbers' => [
                    0 => [
                        'id' => 22,
                        'version' => 1,
                        'licNo' => 'OB1234567',
                        'status' => 'Curtailed',
                        'licenceType' => 'Standard National',
                        'totAuthTrailers' => '4',
                        'totAuthVehicles' => '5',
                        'vehiclesInPossession' => 0,
                        'trailersInPossession' => 4
                    ]
                ]
            ]
        ];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideAllAuthsLoadedData()
    {
        return [];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideAllAuthsExpectedResult()
    {
        return [];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideLeadTcAreaLoadedData()
    {
        return [
            'licence' => [
                'organisation' => [
                    'leadTcArea' => [
                        'name' => 'North East of England'
                    ]
                ]
            ]
        ];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideLeadTcAreaExpectedResult()
    {
        return  [
            'text' => 'North East of England',
        ];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideCurrentSubmissionsLoadedData()
    {
        return [];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideCurrentSubmissionsExpectedResult()
    {
        return [];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideAuthRequestedAppliedForLoadedData()
    {
        return [
            'id' => 24,
            'lastModifiedOn' => null,
            'version' => 1,
            'licence' => [
                'deletedDate' => null,
                'id' => 7,
                'totAuthTrailers' => 4,
                'totAuthVehicles' => 12,
                'version' => 1,
                'applications' => [
                    0 => [
                        'isVariation' => false,
                        'createdOn' => '2015-01-08T11:07:33+0000',
                        'deletedDate' => null,
                        'id' => 1,
                        'receivedDate' => null,
                        'totAuthLargeVehicles' => null,
                        'totAuthMediumVehicles' => null,
                        'totAuthSmallVehicles' => null,
                        'totAuthTrailers' => null,
                        'totAuthVehicles' => null,
                        'version' => 1,
                    ],
                    1 => [
                        'isVariation' => true,
                        'createdOn' => null,
                        'deletedDate' => null,
                        'id' => 2,
                        'receivedDate' => '2014-12-15T10:48:00+0000',
                        'totAuthLargeVehicles' => null,
                        'totAuthMediumVehicles' => null,
                        'totAuthSmallVehicles' => null,
                        'totAuthTrailers' => 5,
                        'totAuthVehicles' => 6,
                        'version' => 1,
                    ],
                ],
            ],
        ];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideAuthRequestedAppliedForExpectedResult()
    {
        return [
            'tables' => [
                'auth-requested-applied-for' => [
                    0 => [
                        'id' => 1,
                        'version' => 1,
                        'currentVehiclesInPossession' => '0',
                        'currentTrailersInPossession' => '0',
                        'currentVehicleAuthorisation' => '0',
                        'currentTrailerAuthorisation' => '0',
                        'requestedVehicleAuthorisation' => '0',
                        'requestedTrailerAuthorisation' => '0'
                    ],
                    1 => [
                        'id' => 2,
                        'version' => 1,
                        'currentVehiclesInPossession' => '0',
                        'currentTrailersInPossession' => '4',
                        'currentVehicleAuthorisation' => '12',
                        'currentTrailerAuthorisation' => '4',
                        'requestedVehicleAuthorisation' => '6',
                        'requestedTrailerAuthorisation' => '5'
                    ]
                ]
            ]
        ];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideTransportManagersLoadedData()
    {
        return [
            'id' => 24,
            'version' => 1,
            'licence' => array (
                'id' => 7,
                'licNo' => 'OB1234567',
                'version' => 1,
                'viAction' => null,
                'tmLicences' =>
                    array (
                        0 =>
                            array (
                                'id' => 1,
                                'version' => 1,
                                'transportManager' =>
                                    array (
                                        'id' => 1,
                                        'version' => 1,
                                        'tmType' =>
                                            array (
                                                'description' => 'Internal'
                                            ),
                                        'workCd' =>
                                            array (
                                                'person' =>
                                                    array (
                                                        'birthDate' => '1975-04-15',
                                                        'familyName' => 'Bond',
                                                        'forename' => 'James',
                                                        'title' => 'Mr'
                                                    ),
                                            ),
                                        'qualifications' =>
                                            array (
                                                0 =>
                                                    array (
                                                        'qualificationType' =>
                                                            array (
                                                                'description' => 'CPCSI'
                                                            ),
                                                    ),
                                                1 =>
                                                    array (
                                                        'qualificationType' =>
                                                            array (
                                                                'description' => 'CPCSN'
                                                            ),
                                                    ),
                                            ),
                                        'otherLicences' =>
                                            array (
                                                0 =>
                                                    array (
                                                        'licNo' => 'AB123456',
                                                        'application' =>
                                                            array (
                                                                'id' => 3
                                                            ),
                                                    ),
                                                1 =>
                                                    array (
                                                        'licNo' => 'YX654321',
                                                        'application' =>
                                                            array (
                                                                'id' => 3
                                                            ),
                                                    ),
                                            ),
                                    ),
                            ),
                        1 =>
                            array (
                                'id' => 2,
                                'version' => 1,
                                'transportManager' =>
                                    array (
                                        'id' => 2,
                                        'version' => 1,
                                        'tmType' =>
                                            array (
                                                'description' => 'External'
                                            ),
                                        'workCd' =>
                                            array (
                                                'person' =>
                                                    array (
                                                        'birthDate' => '1975-04-15',
                                                        'familyName' => 'Smith',
                                                        'forename' => 'Dave',
                                                        'title' => 'Mr'
                                                    ),
                                            ),
                                        'qualifications' =>
                                            array (),
                                        'otherLicences' =>
                                            array (
                                                0 =>
                                                    array (
                                                        'licNo' => 'AB123456',
                                                        'application' =>
                                                            array (
                                                                'id' => 6
                                                            ),
                                                    ),
                                            ),
                                    ),
                            ),
                    ),
            )
        ];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideTransportManagersExpectedResult()
    {
        return [
            'tables' => [
                'transport-managers' => [
                    0 => [
                        'licNo' => 'OB1234567',
                        'id' => 1,
                        'version' => 1,
                        'tmType' => 'Internal',
                        'title' => 'Mr',
                        'forename' => 'James',
                        'familyName' => 'Bond',
                        'dob' => '1975-04-15',
                        'qualifications' => [
                            0 => 'CPCSI',
                            1 => 'CPCSN',
                        ],
                        'otherLicences' => [
                            0 => [
                                'licNo' => 'AB123456',
                                'applicationId' => 3,
                            ],
                            1 => [
                                'licNo' => 'YX654321',
                                'applicationId' => 3,
                            ],
                        ],
                    ],
                    1 => [
                        'licNo' => 'OB1234567',
                        'id' => 2,
                        'version' => 1,
                        'tmType' => 'External',
                        'title' => 'Mr',
                        'forename' => 'Dave',
                        'familyName' => 'Smith',
                        'dob' => '1975-04-15',
                        'qualifications' => [],
                        'otherLicences' => [
                            0 => [
                                'licNo' => 'AB123456',
                                'applicationId' => 6,
                            ],
                        ],
                    ]
                ],
            ]
        ];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideContinuousEffectiveControlLoadedData()
    {
        return [];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideContinuousEffectiveControlExpectedResult()
    {
        return [];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideFitnessAndReputeLoadedData()
    {
        return [];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideFitnessAndReputeExpectedResult()
    {
        return [];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function providePreviousHistoryLoadedData()
    {
        return [];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function providePreviousHistoryExpectedResult()
    {
        return [];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideBusRegAppDetailsLoadedData()
    {
        return [];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideBusRegAppDetailsExpectedResult()
    {
        return [];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideTransportAuthorityCommentsLoadedData()
    {
        return [];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideTransportAuthorityCommentsExpectedResult()
    {
        return [];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideTotalBusRegistrationsLoadedData()
    {
        return [];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideTotalBusRegistrationsExpectedResult()
    {
        return [];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideLocalLicenceHistoryLoadedData()
    {
        return [];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideLocalLicenceHistoryExpectedResult()
    {
        return [];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideLinkedMlhHistoryLoadedData()
    {
        return [];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideLinkedMlhHistoryExpectedResult()
    {
        return [
            'tables' => [
                'linked-licences-app-numbers' => [
                    0 => [
                        'id' => 22,
                        'version' => 1,
                        'licNo' => 'OB1234567',
                        'status' => 'Curtailed',
                        'licenceType' => 'Standard National',
                        'totAuthTrailers' => '4',
                        'totAuthVehicles' => '5',
                        'vehiclesInPossession' => 0,
                        'trailersInPossession' => 4
                    ]
                ]
            ]
        ];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideRegistrationDetailsLoadedData()
    {
        return [];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideRegistrationDetailsExpectedResult()
    {
        return [];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideMaintenanceTachographsHoursLoadedData()
    {
        return [];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideMaintenanceTachographsHoursExpectedResult()
    {
        return [];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideProhibitionHistoryLoadedData()
    {
        return [
            'prohibitionNote' => 'test prohibition_note',
            'prohibitions' => [
                0 => [
                    'id' => 1,
                    'version' => 2,
                    'prohibitionDate' => '2012-03-10T00:00:00+0000',
                    'clearedDate' => '2013-03-10T00:00:00+0000',
                    'vrm' => 'AB123DEF',
                    'isTrailer' => 1,
                    'imposedAt' => 'foo bar',
                    'prohibitionType' => [
                        'description' => 'foo',
                    ]
                ]
            ]
        ];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideProhibitionHistoryExpectedResult()
    {
        return [
            'tables' => [
                'prohibition-history' => [
                    0 => [
                        'id' => 1,
                        'version' => 2,
                        'prohibitionDate' => '2012-03-10T00:00:00+0000',
                        'clearedDate' => '2013-03-10T00:00:00+0000',
                        'vehicle' => 'AB123DEF',
                        'trailer' => 1,
                        'imposedAt' => 'foo bar',
                        'prohibitionType' => 'foo'
                    ]
                ]
            ],
            'text' => 'test prohibition_note'
        ];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideConvictionFpnOffenceHistoryLoadedData()
    {
        return [
            'convictions' => [
                0 => [
                    'id' => 1,
                    'offenceDate' => '2012-03-10T00:00:00+0000',
                    'convictionDate' => '2012-06-15T00:00:00+0100',
                    'operatorName' => 'John Smith Haulage Ltd.',
                    'categoryText' => null,
                    'court' => 'FPN',
                    'penalty' => '3 points on licence',
                    'msi' => 'N',
                    'isDeclared' => 'N',
                    'isDealtWith' => 'N',
                    'defendantType' => [
                        'id' => 'def_t_op',
                        'description' => 'Operator'
                    ],
                    'personFirstname' => '',
                    'personLastname' => '',
                ],
                1 => [
                    'id' => 2,
                    'offenceDate' => '2012-03-10T00:00:00+0000',
                    'convictionDate' => '2012-06-15T00:00:00+0100',
                    'operatorName' => false,
                    'personFirstname' => 'John',
                    'personLastname' => 'Smith',
                    'categoryText' => null,
                    'court' => 'FPN',
                    'penalty' => '3 points on licence',
                    'msi' => 'N',
                    'isDeclared' => 'N',
                    'isDealtWith' => 'N',
                    'defendantType' => [
                        'id' => 'def_t_owner',
                        'description' => 'Owner'
                    ],
                    'personFirstname' => 'Bob',
                    'personLastname' => 'Smith',
                ]
            ]
        ];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideConvictionFpnOffenceHistoryExpectedResult()
    {
        return [
            'tables' => [
                'conviction-fpn-offence-history' => [
                    0 => [
                        'id' => 2,
                        'offenceDate' => '2012-03-10T00:00:00+0000',
                        'convictionDate' => '2012-06-15T00:00:00+0100',
                        'name' => 'Bob Smith',
                        'categoryText' => null,
                        'court' => 'FPN',
                        'penalty' => '3 points on licence',
                        'msi' => 'N',
                        'isDeclared' => 'N',
                        'isDealtWith' => 'N',
                        'defendantType' => [
                            'id' => 'def_t_owner',
                            'description' => 'Owner'
                        ],
                    ],
                    1 => [
                        'id' => 1,
                        'offenceDate' => '2012-03-10T00:00:00+0000',
                        'convictionDate' => '2012-06-15T00:00:00+0100',
                        'name' => 'John Smith Haulage Ltd.',
                        'categoryText' => null,
                        'court' => 'FPN',
                        'penalty' => '3 points on licence',
                        'msi' => 'N',
                        'isDeclared' => 'N',
                        'isDealtWith' => 'N',
                        'defendantType' => [
                            'id' => 'def_t_op',
                            'description' => 'Operator'
                        ],
                    ]
                ]
            ]
        ];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideAnnualTestHistoryLoadedData()
    {
        return [
            'annualTestHistory' => 'test history'
        ];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideAnnualTestHistoryExpectedResult()
    {
        return [
            'text' => 'test history',
        ];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function providePenaltiesLoadedData()
    {
        return [
            'ecmsNo' => '',
            'openDate' => '2014-02-11T00:00:00+0000',
            'description' => '1213213',
            'isImpounding' => 'N',
            'erruOriginatingAuthority' => 'Polish Transport Authority',
            'erruTransportUndertakingName' => 'Polish Transport Authority',
            'erruVrm' => 'GH52 ABC',
            'annualTestHistory' => null,
            'prohibitionNote' => null,
            'penaltiesNote' => 'comment',
            'convictionNote' => null,
            'id' => 29,
            'closeDate' => null,
            'deletedDate' => null,
            'createdOn' => '2014-01-11T11:11:11+0000',
            'lastModifiedOn' => '2014-11-07T12:47:07+0000',
            'version' => 3,
            'seriousInfringements' => [
                0 => [
                    'checkDate' => '2014-04-04',
                    'erruResponseSent' => 'N',
                    'erruResponseTime' => null,
                    'infringementDate' => '2014-04-05',
                    'notificationNumber' => '123456',
                    'reason' => null,
                    'id' => 1,
                    'deletedDate' => null,
                    'createdOn' => '2014-05-04T17:50:06+0100',
                    'lastModifiedOn' => '2014-05-04T17:50:06+0100',
                    'version' => 1,
                    'siCategory' => [
                        'id' => 'MSI',
                        'description' => 'MSI',
                        'deletedDate' => null,
                        'createdOn' => '2011-11-04T17:50:06+0000',
                        'lastModifiedOn' => '2011-11-04T17:50:06+0000',
                        'version' => 1,
                    ],
                    'siCategoryType' => [
                        'id' => '101',
                        'description' => 'Exceeding the maximum six-day or fortnightly driving time limits',
                        'deletedDate' => null,
                        'createdOn' => '2011-11-04T17:50:06+0000',
                        'lastModifiedOn' => '2011-11-04T17:50:06+0000',
                        'version' => 1,
                    ],
                    'appliedPenalties' => [
                        0 => [
                            'imposed' => 'Y',
                            'reasonNotImposed' => null,
                            'id' => 1,
                            'startDate' => '2014-06-01',
                            'endDate' => '2015-01-31',
                            'deletedDate' => null,
                            'createdOn' => '2014-05-21T12:22:09+0100',
                            'lastModifiedOn' => '2014-05-21T12:22:09+0100',
                            'version' => 1,
                            'siPenaltyType' => [
                                'id' => '101',
                                'description' => 'Warning',
                                'deletedDate' => null,
                                'createdOn' => '2013-03-22T17:30:05+0000',
                                'lastModifiedOn' => '2013-03-22T17:30:05+0000',
                                'version' => 1,
                            ],
                            'seriousInfringement' => [
                                'checkDate' => '2014-04-04',
                                'erruResponseSent' => 'N',
                                'erruResponseTime' => null,
                                'infringementDate' => '2014-04-05',
                                'notificationNumber' => '123456',
                                'reason' => null,
                                'id' => 1,
                                'deletedDate' => null,
                                'createdOn' => '2014-05-04T17:50:06+0100',
                                'lastModifiedOn' => '2014-05-04T17:50:06+0100',
                                'version' => 1,
                            ],
                        ],
                        1 => [
                            'imposed' => 'N',
                            'reasonNotImposed' => 'Reason the penalty was not imposed',
                            'id' => 2,
                            'startDate' => '2014-06-01',
                            'endDate' => '2015-01-31',
                            'deletedDate' => null,
                            'createdOn' => '2014-05-21T12:22:09+0100',
                            'lastModifiedOn' => '2014-05-21T12:22:09+0100',
                            'version' => 1,
                            'siPenaltyType' => [
                                'id' => '306',
                                'description' => 'Withdrawal of driver attestations ',
                                'deletedDate' => null,
                                'createdOn' => '2013-03-22T17:30:05+0000',
                                'lastModifiedOn' => '2013-03-22T17:30:05+0000',
                                'version' => 1,
                            ],
                            'seriousInfringement' => [
                                'checkDate' => '2014-04-04',
                                'erruResponseSent' => 'N',
                                'erruResponseTime' => null,
                                'infringementDate' => '2014-04-05',
                                'notificationNumber' => '123456',
                                'reason' => null,
                                'id' => 1,
                                'deletedDate' => null,
                                'createdOn' => '2014-05-04T17:50:06+0100',
                                'lastModifiedOn' => '2014-05-04T17:50:06+0100',
                                'version' => 1,
                            ],
                        ],
                    ],
                    'imposedErrus' => [
                        0 => [
                            'finalDecisionDate' => '2014-10-02',
                            'executed' => true,
                            'id' => 1,
                            'startDate' => '2014-11-01',
                            'endDate' => '2015-12-01',
                            'deletedDate' => null,
                            'createdOn' => '2014-05-21T12:22:09+0100',
                            'lastModifiedOn' => '2014-05-21T12:22:09+0100',
                            'version' => 1,
                            'siPenaltyImposedType' => [
                                'deletedDate' => null,
                                'id' => '204',
                                'description' => 'Immobilisation',
                                'createdOn' => '2013-03-22T17:30:05+0000',
                                'lastModifiedOn' => '2013-03-22T17:30:05+0000',
                                'version' => 1,
                            ],
                        ],
                        1 => [
                            'finalDecisionDate' => '2014-10-02',
                            'executed' => true,
                            'id' => 2,
                            'startDate' => '2014-11-01',
                            'endDate' => '2015-12-01',
                            'deletedDate' => null,
                            'createdOn' => '2014-05-21T12:22:09+0100',
                            'lastModifiedOn' => '2014-05-21T12:22:09+0100',
                            'version' => 1,
                            'siPenaltyImposedType' => [
                                'deletedDate' => null,
                                'id' => '202',
                                'description' => 'Fine',
                                'createdOn' => '2013-03-22T17:30:05+0000',
                                'lastModifiedOn' => '2013-03-22T17:30:05+0000',
                                'version' => 1,
                            ],
                        ],
                    ],
                    'requestedErrus' => [
                        0 => [
                            'duration' => 12,
                            'id' => 1,
                            'deletedDate' => null,
                            'createdOn' => '2014-05-21T12:22:09+0100',
                            'lastModifiedOn' => '2014-05-21T12:22:09+0100',
                            'version' => 1,
                            'siPenaltyRequestedType' => [
                                'id' => '305',
                                'description' => 'Suspension of the issue of driver attestations',
                                'deletedDate' => null,
                                'createdOn' => '2013-03-22T17:30:05+0000',
                                'lastModifiedOn' => '2013-03-22T17:30:05+0000',
                                'version' => 1,
                            ],
                        ],
                        1 => [
                            'duration' => 36,
                            'id' => 2,
                            'deletedDate' => null,
                            'createdOn' => '2014-05-21T12:22:09+0100',
                            'lastModifiedOn' => '2014-05-21T12:22:09+0100',
                            'version' => 1,
                            'siPenaltyRequestedType' => [
                                'id' => '302',
                                'description' => 'foo bar',
                                'deletedDate' => null,
                                'createdOn' => '2013-03-22T17:30:05+0000',
                                'lastModifiedOn' => '2013-03-22T17:30:05+0000',
                                'version' => 1,
                            ],
                        ],
                    ],
                    'memberStateCode' => [
                        'countryDesc' => 'Poland',
                        'isMemberState' => 'N',
                        'id' => 'PL',
                        'createdOn' => null,
                        'lastModifiedOn' => null,
                        'version' => 1,
                    ],
                ],
            ],
        ];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function providePenaltiesExpectedResult()
    {
        return [
            'overview' => [
                'vrm' => 'GH52 ABC',
                'infringementId' => 1,
                'notificationNumber' => '123456',
                'infringementDate' => '2014-04-05',
                'checkDate' => '2014-04-04',
                'category' => 'MSI',
                'categoryType' =>
                    'Exceeding the maximum six-day or fortnightly driving time limits',
                'transportUndertakingName' => 'Polish Transport Authority',
                'memberState' => 'Poland',
                'originatingAuthority' => 'Polish Transport Authority',
            ],
            'tables' => [
                'applied-penalties' => [
                    0 => [
                        'id' => 1,
                        'version' => 1,
                        'penaltyType' => 'Warning',
                        'startDate' => '2014-06-01',
                        'endDate' => '2015-01-31',
                        'imposed' => 'Y',
                    ],
                    1 => [
                        'id' => 2,
                        'version' => 1,
                        'penaltyType' => 'Withdrawal of driver attestations ',
                        'startDate' => '2014-06-01',
                        'endDate' => '2015-01-31',
                        'imposed' => 'N',
                    ],
                ],
                'imposed-penalties' => [
                    0 => [
                        'id' => 1,
                        'version' => 1,
                        'finalDecisionDate' => '2014-10-02',
                        'penaltyType' => 'Immobilisation',
                        'startDate' => '2014-11-01',
                        'endDate' => '2015-12-01',
                        'executed' => true,
                    ],
                    1 => [
                        'id' => 2,
                        'version' => 1,
                        'finalDecisionDate' => '2014-10-02',
                        'penaltyType' => 'Fine',
                        'startDate' => '2014-11-01',
                        'endDate' => '2015-12-01',
                        'executed' => true,
                    ],
                ],
                'requested-penalties' => [
                    0 => [
                        'id' => 1,
                        'version' => 1,
                        'penaltyType' => 'Suspension of the issue of driver attestations',
                        'duration' => 12,
                    ],
                    1 => [
                        'id' => 2,
                        'version' => 1,
                        'penaltyType' => 'foo bar',
                        'duration' => 36,
                    ]
                ]
            ],
            'text' => 'comment',
        ];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideOtherIssuesLoadedData()
    {
        return [];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideOtherIssuesExpectedResult()
    {
        return [];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideTeReportsLoadedData()
    {
        return [];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideTeReportsExpectedResult()
    {
        return [];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideSitePlansLoadedData()
    {
        return [];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideSitePlansExpectedResult()
    {
        return [];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function providePlanningPermissionLoadedData()
    {
        return [];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function providePlanningPermissionExpectedResult()
    {
        return [];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideApplicantsCommentsLoadedData()
    {
        return [];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideApplicantsCommentsExpectedResult()
    {
        return [];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideVisibilityAccessEgressSizeLoadedData()
    {
        return [];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideVisibilityAccessEgressSizeExpectedResult()
    {
        return [];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideCaseComplaintsLoadedData()
    {
        return [];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideCaseComplaintsExpectedResult()
    {
        return [];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideEnvironmentalComplaintsLoadedData()
    {
        return [
            'id' => 24,
            'lastModifiedOn' => null,
            'version' => 1,
            'complaints' => [
                0 => [
                    'complaintDate' => '2015-01-12T10:37:10+0000',
                    'description' => 'Revving engine early in morning',
                    'id' => 7,
                    'version' => 1,
                    'vrm' => 'PRG426F',
                    'status' => [
                        'description' => 'Review Form Sent',
                    ],
                    'complainantContactDetails' => [
                        'person' => [
                            'familyName' => 'Smith',
                            'forename' => 'Jonathan',
                            'title' => 'Mr',
                        ]
                    ],
                    'ocComplaints' => [
                        0 => [
                            'operatingCentre' => [
                                'address' => [
                                    'addressLine1' => 'Unit 5',
                                    'addressLine2' => '12 Albert Street',
                                    'addressLine3' => 'Westpoint',
                                    'addressLine4' => '',
                                    'paonEnd' => null,
                                    'paonStart' => null,
                                    'postcode' => 'LS9 6NA',
                                    'saonEnd' => null,
                                    'saonStart' => null,
                                    'town' => 'Leeds'
                                ]
                            ]
                        ]
                    ],
                    'closeDate' => null,
                ],
                1 => [
                    'complaintDate' => '2014-01-12T10:37:10+0000',
                    'description' => 'complaint 2',
                    'id' => 8,
                    'version' => 3,
                    'vrm' => 'PRG426F',
                    'status' => [
                        'description' => 'Review Form Sent',
                    ],
                    'complainantContactDetails' => [
                        'person' => [
                            'familyName' => 'Smith',
                            'forename' => 'Jonathan',
                            'title' => 'Mr',
                        ]
                    ],
                    'ocComplaints' => [
                        0 => [
                            'operatingCentre' => [
                                'address' => [
                                    'addressLine1' => 'Unit 5',
                                    'addressLine2' => '12 Albert Street',
                                    'addressLine3' => 'Westpoint',
                                    'addressLine4' => '',
                                    'paonEnd' => null,
                                    'paonStart' => null,
                                    'postcode' => 'LS9 6NA',
                                    'saonEnd' => null,
                                    'saonStart' => null,
                                    'town' => 'Leeds'
                                ]
                            ]
                        ]
                    ],
                    'closeDate' => null,
                ]
            ]
        ];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideEnvironmentalComplaintsExpectedResult()
    {
        return [
            'tables' => [
                'environmental-complaints' => [
                    0 => [
                        'id' => 8,
                        'version' => 3,
                        'complainantForename' => 'Jonathan',
                        'complainantFamilyName' => 'Smith',
                        'description' => 'complaint 2',
                        'complaintDate' => '2014-01-12T10:37:10+0000',
                        'status' => 'Review Form Sent',
                        'ocComplaints' => [
                            0 => [
                                'operatingCentre' => [
                                    'address' => [
                                        'addressLine1' => 'Unit 5',
                                        'addressLine2' => '12 Albert Street',
                                        'addressLine3' => 'Westpoint',
                                        'addressLine4' => '',
                                        'paonEnd' => null,
                                        'paonStart' => null,
                                        'postcode' => 'LS9 6NA',
                                        'saonEnd' => null,
                                        'saonStart' => null,
                                        'town' => 'Leeds'
                                    ],
                                ]
                            ],
                        ],
                        'closeDate' => null
                    ],
                    1 => [
                        'id' => 7,
                        'version' => 1,
                        'complainantForename' => 'Jonathan',
                        'complainantFamilyName' => 'Smith',
                        'description' => 'Revving engine early in morning',
                        'complaintDate' => '2015-01-12T10:37:10+0000',
                        'status' => 'Review Form Sent',
                        'ocComplaints' => [
                            0 => [
                                'operatingCentre' => [
                                    'address' => [
                                        'addressLine1' => 'Unit 5',
                                        'addressLine2' => '12 Albert Street',
                                        'addressLine3' => 'Westpoint',
                                        'addressLine4' => '',
                                        'paonEnd' => null,
                                        'paonStart' => null,
                                        'postcode' => 'LS9 6NA',
                                        'saonEnd' => null,
                                        'saonStart' => null,
                                        'town' => 'Leeds'
                                    ],
                                ]
                            ],
                        ],
                        'closeDate' => null
                    ]
                ]
            ]
        ];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideRepresentationsLoadedData()
    {
        return [];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideRepresentationsExpectedResult()
    {
        return [];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideObjectionsLoadedData()
    {
        return [];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideObjectionsExpectedResult()
    {
        return [];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideFinancialInformationLoadedData()
    {
        return [];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideFinancialInformationExpectedResult()
    {
        return [];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideMapsLoadedData()
    {
        return [];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideMapsExpectedResult()
    {
        return [];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideWaiveFeeLateFeeLoadedData()
    {
        return [];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideWaiveFeeLateFeeExpectedResult()
    {
        return [];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideSurrenderLoadedData()
    {
        return [];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideSurrenderExpectedResult()
    {
        return [];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideAnnexLoadedData()
    {
        return [];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideAnnexExpectedResult()
    {
        return [];
    }

    /**
     * The data loaded as a result of submission config bundle database query
     *
     * @return array
     */
    private function provideStatementsLoadedData()
    {
        return [
            'id' => 24,
            'version' => 1,
            'statements' => [
                0 => [
                    'requestedDate' => '2014-01-01T00:00:00+0000',
                    'requestorsBody' => 'Requestors body 1',
                    'stoppedDate' => '2014-05-01T00:00:00+0100',
                    'id' => 1,
                    'issuedDate' => '2014-01-08T00:00:00+0000',
                    'version' => 1,
                    'vrm' => 'VRM 1',
                    'statementType' => [
                        'description' => 'Section 43',
                    ],
                    'requestorsContactDetails' => [
                        'id' => 105,
                        'person' => [
                            'title' => 'Mr',
                            'familyName' => 'Da Ripper',
                            'forename' => 'Jack'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * The data expected as a result of loaded data after filtering
     *
     * @return array
     */
    private function provideStatementsExpectedResult()
    {
        return [
            'tables' => [
                'statements' => [
                    0 => [
                        'id' => 1,
                        'version' => 1,
                        'requestedDate' => '2014-01-01T00:00:00+0000',
                        'requestedBy' => [
                            'title' => 'Mr',
                            'forename' => 'Jack',
                            'familyName' => 'Da Ripper',
                        ],
                        'statementType' => 'Section 43',
                        'stoppedDate' => '2014-05-01T00:00:00+0100',
                        'requestorsBody' => 'Requestors body 1',
                        'issuedDate' => '2014-01-08T00:00:00+0000',
                        'vrm' => 'VRM 1',
                    ]
                ]
            ]
        ];
    }
}
