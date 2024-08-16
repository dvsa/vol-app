<?php

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

/**
 * Class TmOtherEmploymentTest
 * @author Shaun Lizzio <shaun@valtech.co.uk>
 */
class TmOtherEmploymentTest extends AbstractSubmissionSectionTest
{
    protected $submissionSection = \Dvsa\Olcs\Api\Service\Submission\Sections\TmOtherEmployment::class;

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
                    'tm-other-employment' => [
                        0 => [
                            'id' => 1,
                            'version' => 6,
                            'position' => 'Some position',
                            'employerName' => 'Employer name',
                            'address' => [
                                'addressLine1' => '54_a1',
                                'addressLine2' => '54_a2',
                                'addressLine3' => '54_a3',
                                'addressLine4' => null,
                                'town' => '54t',
                                'postcode' => 'pc541PC',
                                'countryCode' => null
                            ],
                            'hoursPerWeek' => 32
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
