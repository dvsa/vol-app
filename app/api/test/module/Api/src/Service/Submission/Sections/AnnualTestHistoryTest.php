<?php

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

/**
 * @covers \Dvsa\Olcs\Api\Service\Submission\Sections\AnnualTestHistory
 */
class AnnualTestHistoryTest extends AbstractSubmissionSectionTest
{
    protected $submissionSection = \Dvsa\Olcs\Api\Service\Submission\Sections\AnnualTestHistory::class;

    /**
     * Filter provider
     *
     * @return array
     */
    public function sectionTestProvider()
    {
        $case = $this->getCase();

        $expectedResult = ['data' => ['text' => 'ath']];

        return [
            [$case, $expectedResult],
        ];
    }
}
