<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

/**
 * Class OppositionsTest
 * @author Shaun Lizzio <shaun@valtech.co.uk>
 */
class OppositionsTest extends AbstractSubmissionSectionTestCase
{
    protected $submissionSection = \Dvsa\Olcs\Api\Service\Submission\Sections\Oppositions::class;

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
                    'oppositions' => [
                        0  => [
                            'id' => 263,
                            'version' => 265,
                            'dateReceived' => '11/12/2013',
                            'oppositionType' => 'opposition_type263-desc',
                            'contactName' => [
                                'title' => 'title-desc',
                                'forename' => 'fn22',
                                'familyName' => 'sn22',
                                'birthDate' => '22/01/1977',
                                'birthPlace' => 'bp'

                            ],
                            'grounds' => [
                                'g1-desc',
                                'g2-desc'
                            ],
                            'isValid' => 1,
                            'isCopied' => 1,
                            'isInTime' => 1,
                            'isWithdrawn' => 0,
                            'isWillingToAttendPi' => 1,
                        ],
                        [
                            'id' => 253,
                            'version' => 255,
                            'dateReceived' => '10/12/2013',
                            'oppositionType' => 'opposition_type253-desc',
                            'contactName' => [
                                'title' => 'title-desc',
                                'forename' => 'fn22',
                                'familyName' => 'sn22',
                                'birthDate' => '22/01/1977',
                                'birthPlace' => 'bp'

                            ],
                            'grounds' => [
                                'g1-desc',
                                'g2-desc'
                            ],
                            'isValid' => 1,
                            'isCopied' => 1,
                            'isInTime' => 1,
                            'isWithdrawn' => 0,
                            'isWillingToAttendPi' => 1,
                        ],
                        [
                            'id' => 243,
                            'version' => 245,
                            'dateReceived' => '',
                            'oppositionType' => 'opposition_type243-desc',
                            'contactName' => [
                                'title' => 'title-desc',
                                'forename' => 'fn22',
                                'familyName' => 'sn22',
                                'birthDate' => '22/01/1977',
                                'birthPlace' => 'bp'
                            ],
                            'grounds' => [
                                'g1-desc',
                                'g2-desc'
                            ],
                            'isValid' => 1,
                            'isCopied' => 1,
                            'isInTime' => 1,
                            'isWithdrawn' => 0,
                            'isWillingToAttendPi' => 1,
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
