<?php

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

/**
 * Class LinkedLicencesTest
 *
 * @author Shaun Lizzio <shaun@valtech.co.uk>
 */
class LinkedLicencesTest extends AbstractSubmissionSectionTest
{
    protected $submissionSection = \Dvsa\Olcs\Api\Service\Submission\Sections\LinkedLicences::class;

    protected $expectedResult = [
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
    public function sectionTestProvider()
    {
        $case = $this->getCase();

        return [
            [$case, $this->expectedResult],
        ];
    }
}
