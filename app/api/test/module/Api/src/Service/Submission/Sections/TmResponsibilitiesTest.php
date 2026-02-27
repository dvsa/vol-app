<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Service\Submission\Sections\TmResponsibilities::class)]
class TmResponsibilitiesTest extends AbstractSubmissionSectionTestCase
{
    protected $submissionSection = \Dvsa\Olcs\Api\Service\Submission\Sections\TmResponsibilities::class;

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
