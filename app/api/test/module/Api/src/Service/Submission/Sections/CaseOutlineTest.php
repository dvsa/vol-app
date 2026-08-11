<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Service\Submission\Sections\CaseOutline;

final class CaseOutlineTest extends AbstractSubmissionSectionTestCase
{
    protected $submissionSection = CaseOutline::class;

    /**
     * Filter provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function sectionTestProvider(): \Iterator
    {
        $case = static::getCase();

        $expectedResult = ['data' => ['text' => 'case description']];

        yield [$case, $expectedResult];
    }
}
