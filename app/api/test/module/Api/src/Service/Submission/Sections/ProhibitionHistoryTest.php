<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Service\Submission\Sections\ProhibitionHistory;

class ProhibitionHistoryTest extends AbstractSubmissionSectionTestCase
{
    protected $submissionSection = ProhibitionHistory::class;

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
