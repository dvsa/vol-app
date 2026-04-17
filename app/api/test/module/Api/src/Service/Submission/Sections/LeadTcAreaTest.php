<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

/**
 * Class LeadTcAreaTest
 * @author Shaun Lizzio <shaun@valtech.co.uk>
 */
class LeadTcAreaTest extends AbstractSubmissionSectionTestCase
{
    protected $submissionSection = \Dvsa\Olcs\Api\Service\Submission\Sections\LeadTcArea::class;

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
                'text' => 'FOO'
            ]
        ];

        return [
            [$case, $expectedResult],
        ];
    }
}
