<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

/**
 * Class ComplianceComplaintsTest
 * @author Shaun Lizzio <shaun@valtech.co.uk>
 */
class ComplianceComplaintsTest extends AbstractSubmissionSectionTestCase
{
    protected $submissionSection = \Dvsa\Olcs\Api\Service\Submission\Sections\ComplianceComplaints::class;

    /**
     * Filter provider
     *
     * @return array
     */
    public static function sectionTestProvider(): array
    {
        $case = static::getCase();

        $expectedResult = [
            'data' => [
                'tables' => [
                    'compliance-complaints' => [
                        [
                            'id' => 563,
                            'version' => 565,
                            'complainantForename' => 'fn22',
                            'complainantFamilyName' => 'sn22',
                            'description' => null,
                            'complaintDate' => '',
                        ],
                        [
                            'id' => 543,
                            'version' => 545,
                            'complainantForename' => 'fn22',
                            'complainantFamilyName' => 'sn22',
                            'description' => null,
                            'complaintDate' => '03/05/2006'
                        ],
                        [
                            'id' => 253,
                            'version' => 255,
                            'complainantForename' => 'fn22',
                            'complainantFamilyName' => 'sn22',
                            'description' => null,
                            'complaintDate' => '04/05/2006'
                        ],
                    ]
                ]
            ]
        ];

        return [
            [$case, $expectedResult],
        ];
    }
}
