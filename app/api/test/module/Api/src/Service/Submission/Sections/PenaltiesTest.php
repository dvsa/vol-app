<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

/**
 * Class PenaltiesTest
 * @author Shaun Lizzio <shaun@valtech.co.uk>
 */
class PenaltiesTest extends AbstractSubmissionSectionTestCase
{
    protected $submissionSection = \Dvsa\Olcs\Api\Service\Submission\Sections\Penalties::class;

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
                'overview' => [
                    'vrm' => 'erruVrm1',
                    'transportUndertakingName' => 'tun',
                    'originatingAuthority' => 'erru_oa',
                    'infringementId' => 734,
                    'notificationNumber' => 'notificationNo',
                    'infringementDate' => '30/11/2009',
                    'checkDate' => '20/07/2010',
                    'category' => 'sicatdesc',
                    'categoryType' => 'sicattypedesc',
                    'memberState' => 'GB-desc'
                ],
                'text' => 'pen-notes1',
                'tables' => [
                    'applied-penalties' => [
                        0 => [
                            'id' => 1,
                            'version' => 6,
                            'penaltyType' => '533-desc',
                            'startDate' => '01/07/2013',
                            'endDate' => '31/08/2013',
                            'imposed' => 'imposed'
                        ]
                    ],
                    'imposed-penalties' => [
                        0 => [
                            'id' => 1,
                            'version' => 23,
                            'penaltyType' => '42-desc',
                            'finalDecisionDate' => '31/12/2014',
                            'startDate' => '01/07/2014',
                            'endDate' => '31/08/2014',
                            'executed' => 'executed'
                        ]
                    ],
                    'requested-penalties' => [
                        0 => [
                            'id' => 1,
                            'version' => 34,
                            'penaltyType' => '952-desc',
                            'duration' => 'duration1'
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
