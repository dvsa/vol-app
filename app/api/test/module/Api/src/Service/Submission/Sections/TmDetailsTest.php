<?php

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

/**
 * Class TmDetailsTest
 * @author Shaun Lizzio <shaun@valtech.co.uk>
 */
class TmDetailsTest extends AbstractSubmissionSectionTest
{
    protected $submissionSection = \Dvsa\Olcs\Api\Service\Submission\Sections\TmDetails::class;

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
                'overview' => [
                    'id' => 43,
                    'title' => 'title-desc',
                    'forename' => 'fn22',
                    'familyName' => 'sn22',
                    'dob' => '22/01/1977',
                    'placeOfBirth' => 'bp',
                    'tmType' => 'tmType-desc',
                    'homeAddress' => [
                        'addressLine1' => '533_a1',
                        'addressLine2' => '533_a2',
                        'addressLine3' => '533_a3',
                        'addressLine4' => null,
                        'town' => '533t',
                        'postcode' => 'pc5331PC',
                        'countryCode' => null
                    ],
                    'emailAddress' => 'blah@blah.com',
                    'workAddress' => [
                        'addressLine1' => '343_a1',
                        'addressLine2' => '343_a2',
                        'addressLine3' => '343_a3',
                        'addressLine4' => null,
                        'town' => '343t',
                        'postcode' => 'pc3431PC',
                        'countryCode' => null
                    ]
                ]
            ]
        ];

        return [
            [$case, $expectedResult],
        ];
    }
}
