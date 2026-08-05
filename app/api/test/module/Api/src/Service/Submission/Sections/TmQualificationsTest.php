<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

/**
 * Class TmQualificationsTest
 * @author Shaun Lizzio <shaun@valtech.co.uk>
 */
final class TmQualificationsTest extends AbstractSubmissionSectionTestCase
{
    protected $submissionSection = \Dvsa\Olcs\Api\Service\Submission\Sections\TmQualifications::class;

    /**
     * Filter provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function sectionTestProvider(): \Iterator
    {
        $case = static::getCase();

        $expectedResult = [
            'data' => [
                'tables' => [
                    'tm-qualifications' => [
                        0 => [
                            'id' => 1,
                            'version' => 5,
                            'qualificationType' => 'tm-qual-desc',
                            'serialNo' => '12344321',
                            'country' => 'GB-desc',
                            'issuedDate' => '04/12/2008'
                        ]
                    ]
                ]
            ]
        ];

        yield [$case, $expectedResult];
    }
}
