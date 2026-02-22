<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

/**
 * Class OutstandingApplicationsTest
 * @to-do this currently doesnt really test anything because the criteria object on licence::getOutstandingApplications
 * seems to work for the application but not for unit testing.
 *
 * @author Shaun Lizzio <shaun@valtech.co.uk>
 */
class OutstandingApplicationsTest extends AbstractSubmissionSectionTestCase
{
    protected $submissionSection = \Dvsa\Olcs\Api\Service\Submission\Sections\OutstandingApplications::class;

    protected const EXPECTED_RESULT = [
        'data' => [
            'tables' => [
                'outstanding-applications' => [
                    0 => [
                        'id' => 1,
                        'version' => 2,
                        'applicationType' => 'New',
                        'receivedDate' => '05/05/2014',
                        'oor' => 'Unknown',
                        'ooo' => 'Unknown',
                        'licNo' => 'OB12345'
                    ],
                    1 => [
                        'id' => 101,
                        'version' => 202,
                        'applicationType' => 'New',
                        'receivedDate' => '05/05/2014',
                        'oor' => 'Unknown',
                        'ooo' => 'Unknown',
                        'licNo' => 'OB12345'
                    ],
                    2 => [
                        'id' => 2,
                        'version' => 4,
                        'applicationType' => 'New',
                        'receivedDate' => '05/05/2014',
                        'oor' => 'Unknown',
                        'ooo' => 'Unknown',
                        'licNo' => 'OB12345'
                    ],
                    3 => [
                        'id' => 102,
                        'version' => 204,
                        'applicationType' => 'New',
                        'receivedDate' => '05/05/2014',
                        'oor' => 'Unknown',
                        'ooo' => 'Unknown',
                        'licNo' => 'OB12345'
                    ],
                    4 => [
                        'id' => 1,
                        'version' => 2,
                        'applicationType' => 'New',
                        'receivedDate' => '05/05/2014',
                        'oor' => 'Unknown',
                        'ooo' => 'Unknown',
                        'licNo' => 'OB12345'
                    ],
                    5 => [
                        'id' => 101,
                        'version' => 202,
                        'applicationType' => 'New',
                        'receivedDate' => '05/05/2014',
                        'oor' => 'Unknown',
                        'ooo' => 'Unknown',
                        'licNo' => 'OB12345'
                    ],
                    6 => [
                        'id' => 2,
                        'version' => 4,
                        'applicationType' => 'New',
                        'receivedDate' => '05/05/2014',
                        'oor' => 'Unknown',
                        'ooo' => 'Unknown',
                        'licNo' => 'OB12345'
                    ],
                    7 => [
                        'id' => 102,
                        'version' => 204,
                        'applicationType' => 'New',
                        'receivedDate' => '05/05/2014',
                        'oor' => 'Unknown',
                        'ooo' => 'Unknown',
                        'licNo' => 'OB12345'
                    ]
                ]
            ]
        ]
    ];

    /**
     * Filter provider
     *
     * @return array
     */
    public static function sectionTestProvider(): array
    {
        $case = static::getCase();

        return [
            [$case, static::EXPECTED_RESULT],
        ];
    }
}
