<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

/**
 * Class LeadTcAreaTest
 * @author Shaun Lizzio <shaun@valtech.co.uk>
 */
final class LeadTcAreaTest extends AbstractSubmissionSectionTestCase
{
    protected $submissionSection = \Dvsa\Olcs\Api\Service\Submission\Sections\LeadTcArea::class;

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
                'text' => 'FOO'
            ]
        ];

        yield [$case, $expectedResult];
    }
}
