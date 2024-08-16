<?php

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

/**
 * Class NoDataCommentsOnlyTest
 * @author Shaun Lizzio <shaun@valtech.co.uk>
 */
class NoDataCommentsOnlyTest extends AbstractSubmissionSectionTest
{
    protected $submissionSection = \Dvsa\Olcs\Api\Service\Submission\Sections\NoDataCommentsOnly::class;

    /**
     * Filter provider
     *
     * @return array
     */
    public function sectionTestProvider()
    {
        $case = $this->getCase();

        $expectedResult = ['data' => []];

        return [
            [$case, $expectedResult],
        ];
    }
}
