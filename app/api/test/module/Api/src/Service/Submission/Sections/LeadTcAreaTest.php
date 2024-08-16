<?php

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

/**
 * Class LeadTcAreaTest
 * @author Shaun Lizzio <shaun@valtech.co.uk>
 */
class LeadTcAreaTest extends AbstractSubmissionSectionTest
{
    protected $submissionSection = \Dvsa\Olcs\Api\Service\Submission\Sections\LeadTcArea::class;

    /**
     * Filter provider
     *
     * @return array
     */
    public function sectionTestProvider()
    {
        $case = $this->getCase();

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
