<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Service\Submission\Sections\TmDetails;

class TmDetailsTest extends AbstractSubmissionSectionTestCase
{
    protected $submissionSection = TmDetails::class;

    public static function sectionTestProvider(): array
    {
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

        $case = static::getCase();

        return [
            [$case, $expectedResult],
        ];
    }
}
