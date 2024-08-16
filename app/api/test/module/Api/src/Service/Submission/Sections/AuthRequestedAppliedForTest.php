<?php

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

/**
 * Class AuthRequestedAppliedForTest
 * @author Shaun Lizzio <shaun@valtech.co.uk>
 */
class AuthRequestedAppliedForTest extends AbstractSubmissionSectionTest
{
    protected $submissionSection = \Dvsa\Olcs\Api\Service\Submission\Sections\AuthRequestedAppliedFor::class;

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
                'tables' => [
                    'auth-requested-applied-for' => [
                        0 => [
                            'id' => 777,
                            'version' => 1554,
                            'currentVehiclesInPossession' => 3,
                            'currentTrailersInPossession' => '0',
                            'currentVehicleAuthorisation' => '0',
                            'currentTrailerAuthorisation' => 5,
                            'requestedVehicleAuthorisation' => '0',
                            'requestedTrailerAuthorisation' => '0',
                        ],
                    ]
                ]
            ]
        ];

        return [
            [$case, $expectedResult],
        ];
    }
}
