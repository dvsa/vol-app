<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking as ConditionUndertakingEntity;
use Dvsa\Olcs\Api\Service\Document\Bookmark\InterimSpecificLicenceConditions;

/**
 * Interim Conditions test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class InterimSpecificLicenceConditionsTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new InterimSpecificLicenceConditions();
        $query = $bookmark->getQuery(['application' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRender(): void
    {
        $bookmark = new InterimSpecificLicenceConditions();
        $bookmark->setData(
            [
                'conditionUndertakings' => [
                    [
                        'id' => 10,
                        'attachedTo' => [
                            'id' => ConditionUndertakingEntity::ATTACHED_TO_LICENCE
                        ],
                        'conditionType' => [
                            'id' => ConditionUndertakingEntity::TYPE_CONDITION
                        ],
                        'isFulfilled' => 'N',
                        'isDraft' => 'N',
                        'notes' => 'a new note',
                        'action' => 'A'
                    ], [
                        'id' => 30,
                        'attachedTo' => [
                            'id' => ConditionUndertakingEntity::ATTACHED_TO_LICENCE
                        ],
                        'conditionType' => [
                            'id' => ConditionUndertakingEntity::TYPE_CONDITION
                        ],
                        'isFulfilled' => 'N',
                        'isDraft' => 'N',
                        'notes' => 'an updated note',
                        'action' => 'U',
                        'licConditionVariation' => [
                            'id' => 20
                        ]
                    ]
                ],
                'licence' => [
                    'conditionUndertakings' => [
                        [
                            'id' => 20,
                            'attachedTo' => [
                                'id' => ConditionUndertakingEntity::ATTACHED_TO_LICENCE
                            ],
                            'conditionType' => [
                                'id' => ConditionUndertakingEntity::TYPE_CONDITION
                            ],
                            'isFulfilled' => 'N',
                            'isDraft' => 'N',
                            'notes' => 'another note',
                            'action' => null
                        ],
                        [
                            'id' => 40,
                            'attachedTo' => [
                                'id' => ConditionUndertakingEntity::ATTACHED_TO_LICENCE
                            ],
                            'conditionType' => [
                                'id' => ConditionUndertakingEntity::TYPE_CONDITION
                            ],
                            'isFulfilled' => 'N',
                            'isDraft' => 'N',
                            'notes' => 'an original note',
                            'action' => null
                        ]
                    ]
                ]
            ]
        );

        $this->assertEquals(
            "an updated note\n\nan original note\n\na new note",
            $bookmark->render()
        );
    }
}
