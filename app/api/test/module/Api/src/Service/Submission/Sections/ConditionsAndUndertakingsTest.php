<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking;

/**
 * Class ConditionsAndUndertakingsTest
 * @author Shaun Lizzio <shaun@valtech.co.uk>
 */
class ConditionsAndUndertakingsTest extends AbstractSubmissionSectionTestCase
{
    protected $submissionSection = \Dvsa\Olcs\Api\Service\Submission\Sections\ConditionsAndUndertakings::class;

    /**
     * Filter provider
     *
     * @return array
     */
    public static function sectionTestProvider(): array
    {
        $case = static::getCase();
        $case->getLicence()->addConditionUndertakings(
            static::generateConditionsUndertakings(
                $case->getLicence(),
                ConditionUndertaking::TYPE_CONDITION,
                999,
                ConditionUndertaking::ADDED_VIA_APPLICATION,
                ConditionUndertaking::ATTACHED_TO_OPERATING_CENTRE,
                new \DateTime('2016-12-20')
            )
        );

        $case->getLicence()->addConditionUndertakings(
            static::generateConditionsUndertakings(
                $case->getLicence(),
                ConditionUndertaking::TYPE_UNDERTAKING,
                35,
                ConditionUndertaking::ADDED_VIA_APPLICATION,
                ConditionUndertaking::ATTACHED_TO_OPERATING_CENTRE,
                new \DateTime('2016-12-21')
            )
        );

        $expectedResult = [
            'data' => [
                'tables' => [
                    'conditions' => [
                        0 => [
                            'id' => 29,
                            'version' => 129,
                            'createdOn' => '23/01/2011',
                            'parentId' => 99,
                            'addedVia' => 'cav_case-desc',
                            'isFulfilled' => 'Y',
                            'isDraft' => 'N',
                            'attachedTo' => 'cat_lic-desc',
                            'notes' => null,
                            'OcAddress' => [],
                        ],
                        1 => [
                            'id' => 58,
                            'version' => 158,
                            'createdOn' => '01/01/2014',
                            'parentId' => 'OB12345',
                            'addedVia' => 'cav_lic-desc',
                            'isFulfilled' => 'Y',
                            'isDraft' => 'N',
                            'attachedTo' => 'cat_lic-desc',
                            'OcAddress' => [],
                            'notes' => null
                        ],
                        2 => [
                            'id' => 999,
                            'version' => 1099,
                            'createdOn' => '20/12/2016',
                            'parentId' => '',
                            'addedVia' => 'cav_app-desc',
                            'isFulfilled' => 'Y',
                            'isDraft' => 'N',
                            'attachedTo' => 'cat_oc-desc',
                            'OcAddress' => [],
                            'notes' => null,
                        ],
                    ],
                    'undertakings' => [
                        0 => [
                            'id' => 34,
                            'version' => 134,
                            'createdOn' => '23/01/2011',
                            'parentId' => 777,
                            'addedVia' => 'cav_app-desc',
                            'isFulfilled' => 'Y',
                            'isDraft' => 'N',
                            'attachedTo' => 'cat_oc-desc',
                            'OcAddress' => [
                                'addressLine1' => '1_a1',
                                'addressLine2' => '1_a2',
                                'addressLine3' => '1_a3',
                                'addressLine4' => null,
                                'town' => '1t',
                                'postcode' => 'pc11PC',
                                'countryCode' => null
                            ],
                            'notes' => null
                        ],
                        1 => [
                            'id' => 35,
                            'version' => 135,
                            'createdOn' => '21/12/2016',
                            'parentId' => '',
                            'addedVia' => 'cav_app-desc',
                            'isFulfilled' => 'Y',
                            'isDraft' => 'N',
                            'attachedTo' => 'cat_oc-desc',
                            'OcAddress' => [],
                            'notes' => null
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
