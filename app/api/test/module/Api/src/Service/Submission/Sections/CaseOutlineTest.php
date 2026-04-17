<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Service\Submission\Sections\CaseOutline;

class CaseOutlineTest extends AbstractSubmissionSectionTestCase
{
    protected $submissionSection = CaseOutline::class;

    /**
     * Filter provider
     *
     * @return array
     */
    public static function sectionTestProvider(): array
    {
        $case = static::getCase();

        $expectedResult = ['data' => ['text' => 'case description']];

        return [
            [$case, $expectedResult],
        ];
    }
}
