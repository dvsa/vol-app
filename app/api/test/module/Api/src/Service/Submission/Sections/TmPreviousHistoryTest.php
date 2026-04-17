<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

/**
 * Class TmPreviousHistoryTest
 * @author Shaun Lizzio <shaun@valtech.co.uk>
 */
class TmPreviousHistoryTest extends AbstractSubmissionSectionTestCase
{
    protected $submissionSection = \Dvsa\Olcs\Api\Service\Submission\Sections\TmPreviousHistory::class;

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
                'tables' => [
                    'convictions-and-penalties' => [
                        0 => [
                            'id' => 1,
                            'version' => 3,
                            'offence' => 'cat-text',
                            'convictionDate' => '03/06/2008',
                            'courtFpn' => 'courtFpn1',
                            'penalty' => 'pen1'
                        ]
                    ],
                    'revoked-curtailed-suspended-licences' => [
                        0 => [
                            'id' => 1,
                            'version' => 3,
                            'licNo' => '1-licNo',
                            'holderName' => '1-holderName'
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
