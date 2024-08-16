<?php

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

/**
 * Class ProhibitionHistoryTest
 * @author Shaun Lizzio <shaun@valtech.co.uk>
 */
class ProhibitionHistoryTest extends AbstractSubmissionSectionTest
{
    protected $submissionSection = \Dvsa\Olcs\Api\Service\Submission\Sections\ProhibitionHistory::class;

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
                'text' => 'prohibition-note',
                'tables' => [
                    'prohibition-history' => [
                        0 => [
                            'id' => 1,
                            'version' => 6,
                            'prohibitionDate' => '11/08/2008',
                            'clearedDate' => '11/08/2012',
                            'prohibitionType' => 'prohibition-type1-desc',
                            'vehicle' => 'VR12 MAB',
                            'trailer' => false,
                            'imposedAt' => 'imposed-at'
                        ]
                    ]
                ]
            ]
        ];

        return [
            [$case, $expectedResult],
        ];
    }
}
