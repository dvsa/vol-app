<?php

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

/**
 * Class CaseOutlineTest
 * @author Shaun Lizzio <shaun@valtech.co.uk>
 */
class CaseOutlineTest extends AbstractSubmissionSectionTest
{
    protected $submissionSection = \Dvsa\Olcs\Api\Service\Submission\Sections\CaseOutline::class;

    /**
     * Filter provider
     *
     * @return array
     */
    public function sectionTestProvider()
    {
        $case = $this->getCase();

        $expectedResult = ['data' => ['text' => 'case description']];

        return [
            [$case, $expectedResult],
        ];
    }
}
