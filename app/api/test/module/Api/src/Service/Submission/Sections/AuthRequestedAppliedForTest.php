<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Service\Submission\Sections\AuthRequestedAppliedFor;

/**
 * Class AuthRequestedAppliedForTest
 * @author Shaun Lizzio <shaun@valtech.co.uk>
 */
class AuthRequestedAppliedForTest extends AbstractSubmissionSectionTestCase
{
    protected $submissionSection = AuthRequestedAppliedFor::class;

    public static function sectionTestProvider(): array
    {
        $case = static::getCase();

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
