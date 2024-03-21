<?php

namespace OlcsTest\Service\Data;

use Common\Service\Data\RefData;
use Olcs\Service\Data\Submission;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Class SubmissionTest
 * @package OlcsTest\Service\Data
 */
class SubmissionTest extends TestCase
{
    /** @var Submission */
    protected $sut;

    /** @var  m\MockInterface */
    private $refDataService;

    protected function setUp(): void
    {
        $this->refDataService = m::mock(RefData::class);

        $this->sut = new Submission($this->refDataService);
    }

    /**
     * @dataProvider providerSubmissions
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
     * @dataProvider providerSubmissions
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
        $mockSectionRefData = $this->getMockSectionRefData();

        $this->refDataService->shouldReceive('fetchListOptions')
            ->with('submission_section')
            ->once()
            ->andReturn($mockSectionRefData);

        $this->assertEquals($this->getMockSectionRefData(), $this->sut->getAllSectionsRefData());

        // check cached
        $this->assertEquals($this->getMockSectionRefData(), $this->sut->getAllSectionsRefData());
    }

    public function testSetAllSectionsRefData()
    {
        $this->sut->setAllSectionsRefData($this->getMockSectionRefData());

        $this->assertEquals($this->getMockSectionRefData(), $this->sut->getAllSectionsRefData());
    }

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

        $this->assertEquals(
            [
                0 => [
                    'submissionSection' => [
                        'id' => 'section_foo',
                    ],
                    'comments' => [
                        0 => 'foo',
                    ]
                ]
            ],
            $this->sut->filterCommentsBySection('section_foo', $comments)
        );

        $this->assertEquals(
            [
                0 => [
                    'submissionSection' => [
                        'id' => 'section_bar',
                    ],
                    'comments' => [
                        1 => 'bar',
                    ]
                ]
            ],
            $this->sut->filterCommentsBySection('section_bar', $comments)
        );
    }

    public function providerSubmissions()
    {
        return [
            [
                [
                    'dataSnapshot' => '{"introduction":{"data":["a"]}}',
                    'submissionSectionComments' => []
                ],
                [
                    'introduction' => [
                        'sectionId' => 'introduction',
                        'description' => 'Introduction',
                        'data' => ['a'],
                        'comments' => []
                    ]
                ]
            ]
        ];
    }

    private function getMockSectionRefData()
    {
        return  [
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
        ];
    }
}
