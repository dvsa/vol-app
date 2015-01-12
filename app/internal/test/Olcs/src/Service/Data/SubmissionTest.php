<?php

namespace OlcsTest\Service\Data;

use Olcs\Service\Data\Submission;
use Mockery as m;

/**
 * Class SubmissionTest
 * @package OlcsTest\Service\Data
 */
class SubmissionTest extends \PHPUnit_Framework_TestCase
{
    protected $sut;

    public function setUp()
    {
        $this->sut = new Submission();
    }

    public function testCreateService()
    {
        $mockRefDataService = $this->getMock('Common\Service\Data\RefData');

        $mockTranslator = $this->getMock('stdClass', ['getLocale']);
        $mockTranslator->expects($this->once())->method('getLocale')->willReturn('en_GB');

        $mockRestClient = $this->getMock('\Common\Util\RestClient', [], [], '', 0);
        $mockRestClient->expects($this->once())->method('setLanguage')->with($this->equalTo('en_GB'));

        $mockApiResolver = $this->getMock('stdClass', ['getClient']);
        $mockApiResolver
            ->expects($this->once())
            ->method('getClient')
            ->with($this->equalTo('Submission'))
            ->willReturn($mockRestClient);

        $mockSl = $this->getMock('\Zend\ServiceManager\ServiceManager');
        $mockSl->expects($this->any())
            ->method('get')
            ->willReturnMap(
                [
                    ['translator', true, $mockTranslator],
                    ['ServiceApiResolver', true, $mockApiResolver],
                    ['Common\Service\Data\RefData', true, $mockRefDataService]
                ]
            );

        $service = $this->sut->createService($mockSl);

        $this->assertInstanceOf('\Olcs\Service\Data\Submission', $service);
        $this->assertSame($mockRestClient, $service->getRestClient());
        $this->assertSame($mockRefDataService, $service->getRefDataService());
    }

    public function testFetchData()
    {
        $submission = ['id' => 24];

        $mockRestClient = $this->getMock('\Common\Util\RestClient', [], [], '', false);
        $mockRestClient->expects($this->once())
            ->method('get')
            ->with($this->equalTo('/24'), $this->isType('array'))
            ->willReturn($submission);

        $this->sut->setRestClient($mockRestClient);

        $this->assertEquals($submission, $this->sut->fetchData(24));
        //test data is cached
        $this->assertEquals($submission, $this->sut->fetchData(24));

    }

    /**
     *
     * @dataProvider providerSubmissions
     * @param $input
     * @param $expected
     */
    public function testExtractSelectedSubmissionSectionsData($input, $expected)
    {
        $mockRefDataService = $this->getMock('Common\Service\Data\RefData');

        $mockSectionRefData = $this->getMockSectionRefData();
        $mockRefDataService->expects(
            $this->once()
        )->method(
            'fetchListOptions'
        )->with('submission_section')
        ->willReturn($mockSectionRefData);

        $this->sut->setRefDataService($mockRefDataService);

        $result = $this->sut->extractSelectedSubmissionSectionsData($input);

        $this->assertEquals($result, $expected);

    }

    /**
     *
     * @dataProvider providerSubmissions
     * @param $input
     */
    public function testExtractSelectedTextOnlySubmissionSectionsData($input)
    {
        $mockRefDataService = $this->getMock('Common\Service\Data\RefData');

        $mockSectionRefData = $this->getMockSectionRefData();
        $mockRefDataService->expects(
            $this->once()
        )->method('fetchListOptions')->with('submission_section')
            ->willReturn($mockSectionRefData);

        $this->sut->setRefDataService($mockRefDataService);
        $this->sut->setSubmissionConfig(
            [
                'sections' => [
                    'introduction' => [
                        'section_type' => ['text']
                    ]
                ]
            ]
        );
        $result = $this->sut->extractSelectedSubmissionSectionsData($input);

        $this->assertArrayHasKey('introduction', $result);
        $this->assertArrayHasKey('data', $result['introduction']);
        $this->assertEmpty($result['introduction']['data']);

    }

    public function testGetAllSectionsRefData()
    {
        $mockRefDataService = $this->getMock('Common\Service\Data\RefData');

        $mockSectionRefData = $this->getMockSectionRefData();
        $mockRefDataService->expects(
            $this->once()
        )->method('fetchListOptions')->with('submission_section')
            ->willReturn($mockSectionRefData);

        $this->sut->setRefDataService($mockRefDataService);

        $result = $this->sut->getAllSectionsRefData();

        $this->assertEquals($result, $this->getMockSectionRefData());

        // check cached
        $result = $this->sut->getAllSectionsRefData();
        $this->assertEquals($result, $this->getMockSectionRefData());

    }

    public function testSetAllSectionsRefData()
    {
        $this->sut->setAllSectionsRefData($this->getMockSectionRefData());
        $result = $this->sut->getAllSectionsRefData();

        $this->assertEquals($this->getMockSectionRefData(), $this->sut->getAllSectionsRefData());
    }

    public function testGetSubmissionTypeTitle()
    {
        $mockRefDataService = $this->getMock('Common\Service\Data\RefData');

        $mockSubmissionTitles = $this->getMockSubmissionTitles();
        $mockRefDataService->expects($this->any())->method('fetchListData')->with('submission_type_title')
            ->willReturn($mockSubmissionTitles);

        $this->sut->setRefDataService($mockRefDataService);

        $testData = $this->getMockSubmissionTitles();
        foreach ($testData as $index => $testTitle) {
            $input = str_replace('_t_', '_o_', $testTitle['id']);

            $result = $this->sut->getSubmissionTypeTitle($input);

            $this->assertEquals($result, $testTitle['description']);
        }
        // test bad data
        $result = $this->sut->getSubmissionTypeTitle('');
        $this->assertEquals($result, '');
        $result = $this->sut->getSubmissionTypeTitle('type_doesnt_exist');
        $this->assertEquals($result, '');
        $result = $this->sut->getSubmissionTypeTitle(false);
        $this->assertEquals($result, '');

    }

    /**
     *
     * @dataProvider providerSubmissionSectionData
     * @param $input
     * @param $expected
     */
    public function testCreateSubmissionSection($input, $expected)
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
     * Tests for submission sections where the data has already been extracted
     *
     * @dataProvider providerSubmissionSectionPrebuiltData
     * @param $input
     * @param $expected
     */
    public function testCreateSubmissionSectionUsingPrebuiltData($input, $expected)
    {
        $this->sut->setLoadedSectionDataForSection('bar', ['foo']);

        $result = $this->sut->loadCaseSectionData($input['caseId'], 'bar', $input['sectionConfig']);

        $this->assertEquals($result, ['foo']);
    }

    /**
     *
     * @dataProvider providerSubmissionSnapshotData
     * @param $input
     * @param $expected
     */
    public function testGenerateSnapshotData($input, $expected)
    {

        $result = $this->sut->generateSnapshotData($input['caseId'], $input['data']);

        $this->assertEquals($result, $expected);
    }

    public function providerSubmissionSnapshotData()
    {
        return [
            [
                [
                    'caseId' => 24,
                    'data' => [
                        'submissionSections' => [
                            'sections' => [
                                'introduction' => 'introduction'
                            ]
                        ]
                    ]
                ],
                [
                    'introduction' => [
                        'data' => []
                    ]
                ]
            ]
        ];
    }

    public function testCreateSubmissionSectionEmptyConfig()
    {

        $input = [
            'caseId' => 24,
            'sectionId' => 'conviction-fpn-offence-history',
            'sectionConfig' => []
        ];

        $result = $this->sut->createSubmissionSection($input['caseId'], $input['sectionId'], $input['sectionConfig']);

        $this->assertEquals($result, []);
    }

    public function testSetId()
    {
        $this->sut->setId(1);
        $this->assertEquals(1, $this->sut->getId());
    }

    public function testGetId()
    {
        $this->assertnull($this->sut->getId());
    }

    public function testSetApiResolver()
    {
        $apiResolver = new \StdClass();
        $this->sut->setApiResolver($apiResolver);
        $this->assertEquals($apiResolver, $this->sut->getApiResolver());
    }

    public function testGetApiResolver()
    {
        $this->assertnull($this->sut->getApiResolver());
    }

    public function testGetLoadedSectionData()
    {
        $this->sut->setLoadedSectionData('foo');
        $this->assertEquals('foo', $this->sut->getLoadedSectionData());
    }

    public function testSetLoadedSectionData()
    {
        $this->assertEquals($this->sut, $this->sut->setLoadedSectionData('foo'));
    }

    public function testSetSubmissionConfig()
    {
        $config = ['foo'];
        $this->sut->setSubmissionConfig($config);
        $this->assertEquals($config, $this->sut->getSubmissionConfig());
    }

    public function testGetSubmissionConfig()
    {
        $this->assertnull($this->sut->getSubmissionConfig());
    }

    /**
     * Tests the filter comments by section
     *
     * @param $input
     * @param $expected
     */
    public function testFilterCommentsBySection()
    {
        $fooComments = [0 => 'foo'];
        $barComments = [1 => 'bar'];
        $comments = [
            0 => [
                'submissionSection' => [
                    'id' => 'section_bar'
                ],
                'comments' => $barComments
            ],

            1 => [
                'submissionSection' => [
                    'id' => 'section_foo'
                ],
                'comments' => $fooComments
            ]
        ];

        $result = $this->sut->filterCommentsBySection('section_foo', $comments);

        $this->assertEquals(
            $result,
            [
                0 => [
                    'submissionSection' => [
                        'id' => 'section_foo',
                    ],
                    'comments' => [
                        0 => 'foo',
                    ]
                ]
            ]
        );

        $result = $this->sut->filterCommentsBySection('section_bar', $comments);
        $this->assertEquals(
            $result,
            [
                0 => [
                    'submissionSection' => [
                        'id' => 'section_bar',
                    ],
                    'comments' => [
                        1 => 'bar',
                    ]
                ]
            ]
        );
    }

    public function testCanClose()
    {
        $id = 99;
        $mockData = [
            'closedDate' => null
        ];
        $mockRestClient = m::mock('Common\Util\RestClient');
        $mockRestClient->shouldReceive('get')->once()->withAnyArgs()->andReturn($mockData);

        $sut = new Submission();
        $sut->setRestClient($mockRestClient);

        $this->assertTrue($sut->canClose($id));
    }

    public function testCanReopen()
    {
        $id = 99;
        $mockData = [
            'closedDate' => null
        ];
        $mockRestClient = m::mock('Common\Util\RestClient');
        $mockRestClient->shouldReceive('get')->once()->withAnyArgs()->andReturn($mockData);

        $sut = new Submission();
        $sut->setRestClient($mockRestClient);

        $this->assertFalse($sut->canReopen($id));
    }

    public function providerSubmissionTitles()
    {
        return [
            [
                    'submission_title_o_mlh',
                    'Introduction'

            ]
        ];
    }

    public function providerSubmissionSectionData()
    {
        return [
            [   // compliance-complaints section
                [
                    'caseId' => 24,
                    'sectionId' => 'compliance-complaints',
                    'sectionConfig' => [
                        'service' => 'Complaints',
                        'filter' => true,
                        'bundle' => ['some_bundle'],
                    ]
                ],
                [
                    'loadedCaseSectionData' => [
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
                    ],
                    'expected' => [
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
                    ]
                ],
            ],
            [   // persons section
                [
                    'caseId' => 24,
                    'sectionId' => 'persons',
                    'sectionConfig' => [
                        'service' => 'Cases',
                        'filter' => true,
                        'bundle' => ['some_bundle'],
                    ]
                ],
                [
                    'loadedCaseSectionData' => [
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
                    ],
                    'expected' => [
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
                    ]
                ],
            ],
            [   // conviction section
                [
                    'caseId' => 24,
                    'sectionId' => 'conviction-fpn-offence-history',
                    'sectionConfig' => [
                        'service' => 'Cases',
                        'filter' => true,
                        'bundle' => ['some_bundle'],
                    ]
                ],
                [
                    'loadedCaseSectionData' => [
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
                    ],
                    'expected' => [
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
                    ]
                ],
            ],
            [   // case-outline
                [ // input
                    'caseId' => 24,
                    'sectionId' => 'case-outline',
                    'sectionConfig' => [
                        'service' => 'Cases',
                        'filter' => true,
                        'bundle' => ['some_bundle'],
                    ]
                ],
                [ // expected
                    'loadedCaseSectionData' => [
                        'description' => 'test description'
                    ],
                    'expected' => [
                        'text' => 'test description',
                    ]
                ]
            ],
            [   // case-summary
                [ // input
                    'caseId' => 24,
                    'sectionId' => 'case-summary',
                    'sectionConfig' => [
                        'service' => 'Cases',
                        'filter' => true,
                        'bundle' => ['some_bundle'],
                    ]
                ],
                [ // expected
                    'loadedCaseSectionData' => $this->getCaseSummaryMockData(),
                    'expected' => [
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
                    ]
                ]
            ],
            [   // opposition section
                [
                    'caseId' => 24,
                    'sectionId' => 'oppositions',
                    'sectionConfig' => [
                        'service' => 'Cases',
                        'filter' => true,
                        'bundle' => ['some_bundle'],
                    ]
                ],
                [
                    'loadedCaseSectionData' => [
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
                    ],
                    'expected' => [
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
                    ]
                ]
            ],
            [   // conditions-undertaking section
                [
                    'caseId' => 24,
                    'sectionId' => 'conditions-and-undertakings',
                    'sectionConfig' => [
                        'service' => 'Cases',
                        'filter' => true,
                        'bundle' => ['some_bundle'],
                    ]
                ],
                [
                    'loadedCaseSectionData' => [
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
                    ],
                    'expected' => [
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
                    ]
                ]
            ],
            [   // linked-licences-app-numbers section
                [
                    'caseId' => 24,
                    'sectionId' => 'linked-licences-app-numbers',
                    'sectionConfig' => [
                        'service' => 'Cases',
                        'filter' => true,
                        'bundle' => ['some_bundle'],
                    ]
                ],
                [
                    'loadedCaseSectionData' => [
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
                    ],
                    'expected' => [
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
                    ]
                ]
            ],
            [   // lead-tc-area section
                [
                    'caseId' => 24,
                    'sectionId' => 'lead-tc-area',
                    'sectionConfig' => [
                        'service' => 'Cases',
                        'filter' => true,
                        'bundle' => ['some_bundle'],
                    ]
                ],
                [
                    'loadedCaseSectionData' => [
                        'licence' => [
                            'organisation' => [
                                'leadTcArea' => [
                                    'name' => 'North East of England'
                                ]
                            ]
                        ]
                    ],
                    'expected' => [
                        'text' => 'North East of England',
                    ]
                ]
            ],
            [
                // prohibition-history section
                [
                    'caseId' => 24,
                    'sectionId' => 'prohibition-history',
                    'sectionConfig' => [
                        'service' => 'Cases',
                        'filter' => true,
                        'bundle' => ['some_bundle'],
                    ]
                ],
                [
                    'loadedCaseSectionData' => [
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
                    ],
                    'expected' => [
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
                    ]
                ]
            ],
            [   // annual-test-history
                [ // input
                    'caseId' => 24,
                    'sectionId' => 'annual-test-history',
                    'sectionConfig' => [
                        'service' => 'Cases',
                        'filter' => true,
                        'bundle' => ['some_bundle'],
                    ]
                ],
                [ // expected
                    'loadedCaseSectionData' => [
                        'annualTestHistory' => 'test history'
                    ],
                    'expected' => [
                        'text' => 'test history',
                    ]
                ]
            ],
            [   // penalties section
                [
                    'caseId' => 24,
                    'sectionId' => 'penalties',
                    'sectionConfig' => [
                        'service' => 'Cases',
                        'filter' => true,
                        'bundle' => ['some_bundle'],
                    ]
                ],
                [
                    'loadedCaseSectionData' => [
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
                    ],
                    'expected' => [
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
                    ]
                ]
            ],
            [
                // auth-requested-applied-for section
                [
                    'caseId' => 24,
                    'sectionId' => 'auth-requested-applied-for',
                    'sectionConfig' => [
                        'service' => 'Cases',
                        'filter' => true,
                        'bundle' => ['some_bundle'],
                    ]
                ],
                [
                    'loadedCaseSectionData' => [
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
                    ],
                    'expected' => [
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
                    ]
                ]
            ],
            [
                // environmental-complaints section
                [
                    'caseId' => 24,
                    'sectionId' => 'environmental-complaints',
                    'sectionConfig' => [
                        'service' => 'Cases',
                        'filter' => true,
                        'bundle' => ['some_bundle'],
                    ]
                ],
                [
                    'loadedCaseSectionData' => [
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
                    ],
                    'expected' => [
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
                    ]
                ]
            ]
        ];
    }

    public function providerSubmissionSectionPrebuiltData()
    {
        return [
            [
                [
                    'caseId' => 24,
                    'sectionId' => 'persons',
                    'sectionConfig' => [
                        'bundle' => 'case-summary',
                    ]
                ],
                [
                    'loadedCaseSectionData' => $this->getCaseSummaryMockData(),
                    'filteredSectionData' => [
                        1 => [
                            'id' => 1,
                            'title' => '',
                            'forename' => 'Tom',
                            'familyName' => 'Jones',
                            'birthDate' => '1972-02-15T00:00:00+0100',
                        ],
                        0 => [
                            'id' => 2,
                            'title' => '',
                            'forename' => 'Keith',
                            'familyName' => 'Winnard',
                            'birthDate' => '1975-03-15T00:00:00+0100',
                        ]
                    ]
                ]
            ]
        ];
    }

    public function providerSubmissions()
    {
        return [
            [
                [
                    'dataSnapshot' =>
                        '{"introduction":{"data":[]}}',
                    'submissionSectionComments' =>
                        []
                ],
                [ 'introduction' => [
                    'sectionId' => 'introduction',
                    'description' => 'Introduction',
                    'data' => [],
                    'comments' => []
                    ]
                ]
            ]
        ];
    }

    private function getMockSubmissionTitles()
    {
        return
            array (
                0 =>
                    array (
                        'description' => 'MLH Submission',
                        'refDataCategoryId' => 'submission_type_title',
                        'olbsKey' => null,
                        'displayOrder' => 1,
                        'id' => 'submission_type_t_mlh',
                        'parent' => null,
                    ),
                1 =>
                    array (
                        'description' => 'Licencing (G) Submission',
                        'refDataCategoryId' => 'submission_type_title',
                        'olbsKey' => null,
                        'displayOrder' => 2,
                        'id' => 'submission_type_t_clo_g',
                        'parent' => null,
                    ),
                2 =>
                    array (
                        'description' => 'Licencing (PSV) Submission',
                        'refDataCategoryId' => 'submission_type_title',
                        'olbsKey' => null,
                        'displayOrder' => 3,
                        'id' => 'submission_type_t_clo_psv',
                        'parent' => null,
                    ),
                3 =>
                    array (
                        'description' => 'Licencing Fees Submission',
                        'refDataCategoryId' => 'submission_type_title',
                        'olbsKey' => null,
                        'displayOrder' => 4,
                        'id' => 'submission_type_t_clo_fep',
                        'parent' => null,
                    ),
                4 =>
                    array (
                        'description' => 'Compliance submission',
                        'refDataCategoryId' => 'submission_type_title',
                        'olbsKey' => null,
                        'displayOrder' => 5,
                        'id' => 'submission_type_t_otc',
                        'parent' => null,
                    ),
                5 =>
                    array (
                        'description' => 'ENV Submission',
                        'refDataCategoryId' => 'submission_type_title',
                        'olbsKey' => null,
                        'displayOrder' => 6,
                        'id' => 'submission_type_t_env',
                        'parent' => null,
                    ),
                6 =>
                    array (
                        'description' => 'IRFO Submission',
                        'refDataCategoryId' => 'submission_type_title',
                        'olbsKey' => null,
                        'displayOrder' => 7,
                        'id' => 'submission_type_t_irfo',
                        'parent' => null,
                    ),
                7 =>
                    array (
                        'description' => 'Bus Registration Submission',
                        'refDataCategoryId' => 'submission_type_title',
                        'olbsKey' => null,
                        'displayOrder' => 8,
                        'id' => 'submission_type_t_bus_reg',
                        'parent' => null,
                    ),
                8 =>
                    array (
                        'description' => 'TM Only Submission',
                        'refDataCategoryId' => 'submission_type_title',
                        'olbsKey' => null,
                        'displayOrder' => 9,
                        'id' => 'submission_type_t_tm',
                        'parent' => null,
                    ),
            );
    }

    private function getMockSectionRefData()
    {
        return array (
            'introduction' => 'Introduction',
            'case-summary' => 'Case summary',
            'case-outline' => 'Case outline',
            'most-serious-infringement' => 'Most serious infringement',
            'persons' => 'Persons',
            'operating-centres' => 'Operating centres',
            'operating-centre-history' => 'Operating centre history',
            'conditions-and-undertakings' => 'Conditions and undertakings',
            'intelligence-unit-check' => 'Intelligence unit check',
            'interim' => 'Interim',
            'advertisement' => 'Advertisement',
            'linked-licences-app-numbers' => 'Linked licences & application numbers',
            'all-auths' => 'All auths',
            'lead-tc-area' => 'Lead TC area',
            'current-submissions' => 'Current submissions',
            'auth-requested-applied-for' => 'Authorisation requested / applied for',
            'transport-managers' => 'Transport managers',
            'continuous-effective-control' => 'Continuous and effective control',
            'fitness-and-repute' => 'Fitness & repute',
            'previous-history' => 'Previous history',
            'bus-reg-app-details' => 'Bus registration application details',
            'transport-authority-comments' => 'Transport authority comments',
            'total-bus-registrations' => 'Total bus registrations',
            'local-licence-history' => 'Local licence history',
            'linked-mlh-history' => 'Linked MLH history',
            'registration-details' => 'Registration details',
            'maintenance-tachographs-hours' => 'Maintenance / Tachographs / Drivers hours',
            'prohibition-history' => 'Prohibition history',
            'conviction-fpn-offence-history' => 'Conviction / FPN / Offence history',
            'annual-test-history' => 'Annual test history',
            'penalties' => 'Penalties',
            'other-issues' => 'Other issues / misc',
            'te-reports' => 'TE reports',
            'site-plans' => 'Site plans',
            'planning-permission' => 'Planning permission',
            'applicants-comments' => 'Applicants comments',
            'visibility-access-egress-size' => 'Visibility / access egress size',
            'case-complaints' => 'Case complaints',
            'environmental-complaints' => 'Environmental complaints',
            'representations' => 'Representations',
            'objections' => 'Objections',
            'financial-information' => 'Financial information',
            'maps' => 'Maps',
            'waive-fee-late-fee' => 'Waive fee / Late fee',
            'surrender' => 'Surrender',
            'annex' => 'Annex',
        );
    }

    private function getCaseSummaryMockData()
    {
        return [
            'Results' => [ // branch test
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
                ],
            ]
        ];
    }
}
