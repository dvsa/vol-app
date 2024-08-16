<?php

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

/**
 * @covers \Dvsa\Olcs\Api\Service\Submission\Sections\TmResponsibilities
 */
class TmResponsibilitiesTest extends AbstractSubmissionSectionTest
{
    protected $submissionSection = \Dvsa\Olcs\Api\Service\Submission\Sections\TmResponsibilities::class;

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
                    'applications' => [
                        0 => [
                            'id' => 522,
                            'version' => 1,
                            'managerType' => 'tmType-desc',
                            'hrsPerWeek' => 28,
                            'applicationId' => 852,
                            'organisationName' => 'Org name',
                            'status' => 'apsts_granted-desc',
                            'licNo' => 'OB12345'
                        ]
                    ],
                    'licences' => [
                        0 => [
                            'id' => 234,
                            'version' => 1,
                            'managerType' => 'tmType-desc',
                            'hrsPerWeek' => 28,
                            'licenceId' => 7,
                            'organisationName' => 'Org name',
                            'status' => 'lic_status-desc',
                            'licNo' => 'OB12345'
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
