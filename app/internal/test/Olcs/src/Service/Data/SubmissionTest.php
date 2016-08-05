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
        $mockSectionRefData = $this->getMockSectionRefData();

        $submissionConfig = [
            'sections' => [
                'introduction' => [
                    'section_type' => ['anything']
                ]
            ]
        ];

        $result = $this->sut->extractSelectedSubmissionSectionsData($input, $mockSectionRefData, $submissionConfig);

        $this->assertEquals($result, $expected);
    }

    /**
     *
     * @dataProvider providerSubmissions
     * @param $input
     */
    public function testExtractSelectedTextOnlySubmissionSectionsData($input)
    {
        $mockSectionRefData = $this->getMockSectionRefData();

        $submissionConfig = [
            'sections' => [
                'introduction' => [
                    'section_type' => ['text']
                ]
            ]
        ];

        $result = $this->sut->extractSelectedSubmissionSectionsData($input, $mockSectionRefData, $submissionConfig);

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

    public function providerSubmissionSectionData()
    {
        return [
            [
            'compliance-complaints',
            'persons',
            'conviction',
            'case-outline',
            'case-summary',
            'opposition',
            'conditions-undertaking',
            'linked-licences-app-numbers',
            'lead-tc-area',
            'prohibition-history',
            'annual-test-history',
            'penalties',
            'auth-requested-applied-for',
            'environmental-complaints',
            'outstanding-applications',
            'statements',
            'transport-managers',
            'operating-centres'
            ]
        ];
    }

    /**
     * Tests for submission sections where the data has already been extracted
     */
    public function testCreateSubmissionSectionUsingPrebuiltData()
    {
        $input = [
            'caseId' => 24,
            'sectionId' => 'persons',
            'sectionConfig' => [
                'bundle' => 'case-summary',
            ]
        ];

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

    public function providerSubmissions()
    {
        return [
            [
                [
                    'dataSnapshot' =>
                        '{"introduction":{"data":["a"]}}',
                    'submissionSectionComments' =>
                        []
                ],
                [ 'introduction' => [
                    'sectionId' => 'introduction',
                    'description' => 'Introduction',
                    'data' => ['a'],
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
            'outstanding-applications' => 'Outstanding applications',
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
            'statements' => 'Statements'
        );
    }
}
