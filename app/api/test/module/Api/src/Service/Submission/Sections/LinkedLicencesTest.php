<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

/**
 * Class LinkedLicencesTest
 *
 * @author Shaun Lizzio <shaun@valtech.co.uk>
 */
class LinkedLicencesTest extends AbstractSubmissionSectionTestCase
{
    protected $submissionSection = \Dvsa\Olcs\Api\Service\Submission\Sections\LinkedLicences::class;

    protected const EXPECTED_RESULT = [
        'data' => [
            'tables' => [
                'linked-licences-app-numbers' => [
                    [
                        'id' => 1,
                        'version' => 1,
                        'licNo' => 'OB12345',
                        'status' => 'lic_status-desc',
                        'licenceType' => 'lic_type-desc',
                        'totAuthTrailers' => 5,
                        'totAuthVehicles' => null,
                        'vehiclesInPossession' => 3,
                        'trailersInPossession' => 5
                    ],
                    [
                        'id' => 2,
                        'version' => 2,
                        'licNo' => 'OB12345',
                        'status' => 'lic_status-desc',
                        'licenceType' => 'lic_type-desc',
                        'totAuthTrailers' => 5,
                        'totAuthVehicles' => null,
                        'vehiclesInPossession' => 3,
                        'trailersInPossession' => 5
                    ]
                ]
            ]
        ]
    ];

    /**
     * Filter provider
     *
     * @return array
     */
    public static function sectionTestProvider(): array
    {
        $case = static::getCase();

        return [
            [$case, static::EXPECTED_RESULT],
        ];
    }
}
