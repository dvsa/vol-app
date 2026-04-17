<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Entity\Cases\Conviction;

/**
 * Class ConvictionFpnOffenceHistoryTest
 * @author Shaun Lizzio <shaun@valtech.co.uk>
 */
class ConvictionFpnOffenceHistoryTest extends AbstractSubmissionSectionTestCase
{
    protected $submissionSection = \Dvsa\Olcs\Api\Service\Submission\Sections\ConvictionFpnOffenceHistory::class;

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
                'text' => 'conv_note1',
                'tables' => [
                    'conviction-fpn-offence-history' => [
                        0 => [
                            'id' => 734,
                            'version' => 736,
                            'offenceDate' => '03/06/2007',
                            'convictionDate' => '03/06/2008',
                            'defendantType' => Conviction::DEFENDANT_TYPE_ORGANISATION . '-desc',
                            'name' => 'operator1',
                            'categoryText' => 'cat-text',
                            'court' => 'court1',
                            'penalty' => 'pen1',
                            'msi' => 'msi1',
                            'isDeclared' => false,
                            'isDealtWith' => true
                        ],
                        1 => [
                            'id' => 734,
                            'version' => 736,
                            'offenceDate' => '03/06/2007',
                            'convictionDate' => '03/06/2008',
                            'defendantType' => Conviction::DEFENDANT_TYPE_DIRECTOR . '-desc',
                            'name' => 'fn sn',
                            'categoryText' => 'cat-text',
                            'court' => 'court1',
                            'penalty' => 'pen1',
                            'msi' => 'msi1',
                            'isDeclared' => false,
                            'isDealtWith' => true
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
