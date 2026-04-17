<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

/**
 * Class PeopleTest
 * @author Shaun Lizzio <shaun@valtech.co.uk>
 */
class PeopleTest extends AbstractSubmissionSectionTestCase
{
    protected $submissionSection = \Dvsa\Olcs\Api\Service\Submission\Sections\People::class;

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
                    'people' => [
                        1 => [
                            'id' => 1,
                            'title' => 'title-desc',
                            'forename' => 'fn1',
                            'familyName' => 'sn1',
                            'birthDate' => '01/01/1977',
                            'disqualificationStatus' => 'None'
                        ]
                    ]
                ]
            ]
        ];

        return [
            [$case, $expectedResult],
        ];
    }
}
