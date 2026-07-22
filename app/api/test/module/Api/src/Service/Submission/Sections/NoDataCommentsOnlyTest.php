<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

/**
 * Class NoDataCommentsOnlyTest
 * @author Shaun Lizzio <shaun@valtech.co.uk>
 */
final class NoDataCommentsOnlyTest extends AbstractSubmissionSectionTestCase
{
    protected $submissionSection = \Dvsa\Olcs\Api\Service\Submission\Sections\NoDataCommentsOnly::class;

    /**
     * Filter provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function sectionTestProvider(): \Iterator
    {
        $case = static::getCase();

        $expectedResult = ['data' => []];

        yield [$case, $expectedResult];
    }
}
