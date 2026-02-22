<?php

declare(strict_types=1);

/**
 * Conditions Undertakings Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\ConditionsUndertakingsReviewService;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking as Condition;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Conditions Undertakings Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ConditionsUndertakingsReviewServiceTest extends MockeryTestCase
{
    protected $sut;

    public function setUp(): void
    {
        $mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->sut = new ConditionsUndertakingsReviewService($abstractReviewServiceServices);
    }

    public function testGetConfigFromData(): void
    {
        $this->assertEmpty($this->sut->getConfigFromData([]));
    }

    public function testFormatLicencesSubSection(): void
    {
        // Params
        $list = [
            ['notes' => '123'],
            ['notes' => '456'],
            ['notes' => '789']
        ];
        $lva = 'application';
        $conditionOrUndertaking = 'conditions';
        $action = 'added';
        $expected = [
            'title' => 'application-review-conditions-undertakings-licence-conditions-added',
            'mainItems' => [
                [
                    'multiItems' => [
                        [
                            [
                                'list' => ['123', '456', '789']
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->assertEquals(
            $expected,
            $this->sut->formatLicenceSubSection($list, $lva, $conditionOrUndertaking, $action)
        );
    }

    public function testFormatOcSubSection(): void
    {
        // Params
        $list = [
            [
                [
                    'notes' => '123',
                    'operatingCentre' => [
                        'address' => [
                            'addressLine1' => '123 street',
                            'town' => 'Footown'
                        ]
                    ]
                ],
                ['notes' => '456'],
                ['notes' => '789']
            ],
            [
                [
                    'notes' => '123',
                    'operatingCentre' => [
                        'address' => [
                            'addressLine1' => '321 street',
                            'town' => 'Footown'
                        ]
                    ]
                ]
            ]
        ];
        $lva = 'application';
        $conditionOrUndertaking = 'conditions';
        $action = 'added';
        $expected = [
            'title' => 'application-review-conditions-undertakings-oc-conditions-added',
            'mainItems' => [
                [
                    'header' => '123 street, Footown',
                    'multiItems' => [
                        [
                            [
                                'list' => ['123', '456', '789']
                            ]
                        ]
                    ]
                ],
                [
                    'header' => '321 street, Footown',
                    'multiItems' => [
                        [
                            [
                                'list' => ['123']
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $this->assertEquals(
            $expected,
            $this->sut->formatOcSubSection($list, $lva, $conditionOrUndertaking, $action)
        );
    }

    public function testFormatConditionsList(): void
    {
        // Params
        $conditions = [
            ['notes' => 'abc'],
            ['notes' => 'def']
        ];
        $expected = ['abc', 'def'];

        $this->assertEquals($expected, $this->sut->formatConditionsList($conditions));
    }

    public function testSplitUpConditionsAndUndertakingsWithEmptyLists(): void
    {
        // Params
        $data = [
            'conditionUndertakings' => []
        ];
        $expectedLicConds = [];
        $expectedLicUnds = [];
        $expectedOcConds = [];
        $expectedOcUnds = [];

        [$licConds, $licUnds, $ocConds, $ocUnds] = $this->sut->splitUpConditionsAndUndertakings($data);

        $this->assertEquals($expectedLicConds, $licConds);
        $this->assertEquals($expectedLicUnds, $licUnds);
        $this->assertEquals($expectedOcConds, $ocConds);
        $this->assertEquals($expectedOcUnds, $ocUnds);
    }

    public function testSplitUpConditionsAndUndertakings(): void
    {
        // Params
        $data = [
            'conditionUndertakings' => [
                // Added licence conditions
                [
                    'action' => 'A',
                    'notes' => 'Added licence condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                [
                    'action' => 'A',
                    'notes' => 'Another added licence condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                // Updated licence conditions
                [
                    'action' => 'U',
                    'notes' => 'Updated licence condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                [
                    'action' => 'U',
                    'notes' => 'Another updated licence condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                // Deleted licence conditions
                [
                    'action' => 'D',
                    'notes' => 'Deleted licence condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                [
                    'action' => 'D',
                    'notes' => 'Another deleted licence condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                // Added licence undertakings
                [
                    'action' => 'A',
                    'notes' => 'Added licence undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                [
                    'action' => 'A',
                    'notes' => 'Another added licence undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                // Updated licence undertakings
                [
                    'action' => 'U',
                    'notes' => 'Updated licence undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                [
                    'action' => 'U',
                    'notes' => 'Another updated licence undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                // Deleted licence undertakings
                [
                    'action' => 'D',
                    'notes' => 'Deleted licence undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                [
                    'action' => 'D',
                    'notes' => 'Another deleted licence undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                // Added oc conditions
                [
                    'action' => 'A',
                    'notes' => 'Added oc condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 111]
                ],
                [
                    'action' => 'A',
                    'notes' => 'Another added oc condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 111]
                ],
                [
                    'action' => 'A',
                    'notes' => 'Another added oc condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 222]
                ],
                // Updated oc conditions
                [
                    'action' => 'U',
                    'notes' => 'Updated oc condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 111]
                ],
                [
                    'action' => 'U',
                    'notes' => 'Another updated oc condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 111]
                ],
                [
                    'action' => 'U',
                    'notes' => 'Another updated oc condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 222]
                ],
                // Deleted oc conditions
                [
                    'action' => 'D',
                    'notes' => 'Deleted oc condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 111]
                ],
                [
                    'action' => 'D',
                    'notes' => 'Another deleted oc condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 111]
                ],
                [
                    'action' => 'D',
                    'notes' => 'Another deleted oc condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 222]
                ],
                // Added oc undertakings
                [
                    'action' => 'A',
                    'notes' => 'Added oc undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 111]
                ],
                [
                    'action' => 'A',
                    'notes' => 'Another added oc undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 111]
                ],
                [
                    'action' => 'A',
                    'notes' => 'Another added oc undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 222]
                ],
                // Updated oc undertakings
                [
                    'action' => 'U',
                    'notes' => 'Updated oc undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 111]
                ],
                [
                    'action' => 'U',
                    'notes' => 'Another updated oc undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 111]
                ],
                [
                    'action' => 'U',
                    'notes' => 'Another updated oc undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 222]
                ],
                // Deleted oc undertakings
                [
                    'action' => 'D',
                    'notes' => 'Deleted oc undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 111]
                ],
                [
                    'action' => 'D',
                    'notes' => 'Another deleted oc undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 111]
                ],
                [
                    'action' => 'D',
                    'notes' => 'Another deleted oc undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 222]
                ],
            ]
        ];
        $expectedLicConds = [
            'A' => [
                [
                    'action' => 'A',
                    'notes' => 'Added licence condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                [
                    'action' => 'A',
                    'notes' => 'Another added licence condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ]
            ],
            'U' => [
                [
                    'action' => 'U',
                    'notes' => 'Updated licence condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                [
                    'action' => 'U',
                    'notes' => 'Another updated licence condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ]
            ],
            'D' => [
                [
                    'action' => 'D',
                    'notes' => 'Deleted licence condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                [
                    'action' => 'D',
                    'notes' => 'Another deleted licence condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ]
            ]
        ];
        $expectedLicUnds = [
            'A' => [
                [
                    'action' => 'A',
                    'notes' => 'Added licence undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                [
                    'action' => 'A',
                    'notes' => 'Another added licence undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ]
            ],
            'U' => [
                [
                    'action' => 'U',
                    'notes' => 'Updated licence undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                [
                    'action' => 'U',
                    'notes' => 'Another updated licence undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ]
            ],
            'D' => [
                [
                    'action' => 'D',
                    'notes' => 'Deleted licence undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                [
                    'action' => 'D',
                    'notes' => 'Another deleted licence undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ]
            ]
        ];
        $expectedOcConds = [
            'A' => [
                111 => [
                    [
                        'action' => 'A',
                        'notes' => 'Added oc condition',
                        'conditionType' => ['id' => Condition::TYPE_CONDITION],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 111]
                    ],
                    [
                        'action' => 'A',
                        'notes' => 'Another added oc condition',
                        'conditionType' => ['id' => Condition::TYPE_CONDITION],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 111]
                    ]
                ],
                222 => [
                    [
                        'action' => 'A',
                        'notes' => 'Another added oc condition',
                        'conditionType' => ['id' => Condition::TYPE_CONDITION],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 222]
                    ],
                ]
            ],
            'U' => [
                111 => [
                    [
                        'action' => 'U',
                        'notes' => 'Updated oc condition',
                        'conditionType' => ['id' => Condition::TYPE_CONDITION],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 111]
                    ],
                    [
                        'action' => 'U',
                        'notes' => 'Another updated oc condition',
                        'conditionType' => ['id' => Condition::TYPE_CONDITION],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 111]
                    ],
                ],
                222 => [
                    [
                        'action' => 'U',
                        'notes' => 'Another updated oc condition',
                        'conditionType' => ['id' => Condition::TYPE_CONDITION],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 222]
                    ],
                ]
            ],
            'D' => [
                111 => [
                    [
                        'action' => 'D',
                        'notes' => 'Deleted oc condition',
                        'conditionType' => ['id' => Condition::TYPE_CONDITION],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 111]
                    ],
                    [
                        'action' => 'D',
                        'notes' => 'Another deleted oc condition',
                        'conditionType' => ['id' => Condition::TYPE_CONDITION],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 111]
                    ],
                ],
                222 => [
                    [
                        'action' => 'D',
                        'notes' => 'Another deleted oc condition',
                        'conditionType' => ['id' => Condition::TYPE_CONDITION],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 222]
                    ],
                ]
            ]
        ];
        $expectedOcUnds = [
            'A' => [
                111 => [
                    [
                        'action' => 'A',
                        'notes' => 'Added oc undertaking',
                        'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 111]
                    ],
                    [
                        'action' => 'A',
                        'notes' => 'Another added oc undertaking',
                        'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 111]
                    ]
                ],
                222 => [
                    [
                        'action' => 'A',
                        'notes' => 'Another added oc undertaking',
                        'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 222]
                    ],
                ]
            ],
            'U' => [
                111 => [
                    [
                        'action' => 'U',
                        'notes' => 'Updated oc undertaking',
                        'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 111]
                    ],
                    [
                        'action' => 'U',
                        'notes' => 'Another updated oc undertaking',
                        'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 111]
                    ],
                ],
                222 => [
                    [
                        'action' => 'U',
                        'notes' => 'Another updated oc undertaking',
                        'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 222]
                    ],
                ]
            ],
            'D' => [
                111 => [
                    [
                        'action' => 'D',
                        'notes' => 'Deleted oc undertaking',
                        'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 111]
                    ],
                    [
                        'action' => 'D',
                        'notes' => 'Another deleted oc undertaking',
                        'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 111]
                    ],
                ],
                222 => [
                    [
                        'action' => 'D',
                        'notes' => 'Another deleted oc undertaking',
                        'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 222]
                    ],
                ]
            ]
        ];

        [$licConds, $licUnds, $ocConds, $ocUnds] = $this->sut->splitUpConditionsAndUndertakings($data);

        $this->assertEquals($expectedLicConds, $licConds);
        $this->assertEquals($expectedLicUnds, $licUnds);
        $this->assertEquals($expectedOcConds, $ocConds);
        $this->assertEquals($expectedOcUnds, $ocUnds);
    }

    public function testSplitUpConditionsAndUndertakingsWithoutAction(): void
    {
        // Params
        $data = [
            'conditionUndertakings' => [
                // Added licence conditions
                [
                    'action' => 'A',
                    'notes' => 'Added licence condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                [
                    'action' => 'A',
                    'notes' => 'Another added licence condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                // Updated licence conditions
                [
                    'action' => 'U',
                    'notes' => 'Updated licence condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                [
                    'action' => 'U',
                    'notes' => 'Another updated licence condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                // Deleted licence conditions
                [
                    'action' => 'D',
                    'notes' => 'Deleted licence condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                [
                    'action' => 'D',
                    'notes' => 'Another deleted licence condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                // Added licence undertakings
                [
                    'action' => 'A',
                    'notes' => 'Added licence undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                [
                    'action' => 'A',
                    'notes' => 'Another added licence undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                // Updated licence undertakings
                [
                    'action' => 'U',
                    'notes' => 'Updated licence undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                [
                    'action' => 'U',
                    'notes' => 'Another updated licence undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                // Deleted licence undertakings
                [
                    'action' => 'D',
                    'notes' => 'Deleted licence undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                [
                    'action' => 'D',
                    'notes' => 'Another deleted licence undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                // Added oc conditions
                [
                    'action' => 'A',
                    'notes' => 'Added oc condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 111]
                ],
                [
                    'action' => 'A',
                    'notes' => 'Another added oc condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 111]
                ],
                [
                    'action' => 'A',
                    'notes' => 'Another added oc condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 222]
                ],
                // Updated oc conditions
                [
                    'action' => 'U',
                    'notes' => 'Updated oc condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 111]
                ],
                [
                    'action' => 'U',
                    'notes' => 'Another updated oc condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 111]
                ],
                [
                    'action' => 'U',
                    'notes' => 'Another updated oc condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 222]
                ],
                // Deleted oc conditions
                [
                    'action' => 'D',
                    'notes' => 'Deleted oc condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 111]
                ],
                [
                    'action' => 'D',
                    'notes' => 'Another deleted oc condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 111]
                ],
                [
                    'action' => 'D',
                    'notes' => 'Another deleted oc condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 222]
                ],
                // Added oc undertakings
                [
                    'action' => 'A',
                    'notes' => 'Added oc undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 111]
                ],
                [
                    'action' => 'A',
                    'notes' => 'Another added oc undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 111]
                ],
                [
                    'action' => 'A',
                    'notes' => 'Another added oc undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 222]
                ],
                // Updated oc undertakings
                [
                    'action' => 'U',
                    'notes' => 'Updated oc undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 111]
                ],
                [
                    'action' => 'U',
                    'notes' => 'Another updated oc undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 111]
                ],
                [
                    'action' => 'U',
                    'notes' => 'Another updated oc undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 222]
                ],
                // Deleted oc undertakings
                [
                    'action' => 'D',
                    'notes' => 'Deleted oc undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 111]
                ],
                [
                    'action' => 'D',
                    'notes' => 'Another deleted oc undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 111]
                ],
                [
                    'action' => 'D',
                    'notes' => 'Another deleted oc undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                    'operatingCentre' => ['id' => 222]
                ],
            ]
        ];
        $expectedLicConds = [
            'list' => [
                [
                    'action' => 'A',
                    'notes' => 'Added licence condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                [
                    'action' => 'A',
                    'notes' => 'Another added licence condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                [
                    'action' => 'U',
                    'notes' => 'Updated licence condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                [
                    'action' => 'U',
                    'notes' => 'Another updated licence condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                [
                    'action' => 'D',
                    'notes' => 'Deleted licence condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                [
                    'action' => 'D',
                    'notes' => 'Another deleted licence condition',
                    'conditionType' => ['id' => Condition::TYPE_CONDITION],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ]
            ]
        ];
        $expectedLicUnds = [
            'list' => [
                [
                    'action' => 'A',
                    'notes' => 'Added licence undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                [
                    'action' => 'A',
                    'notes' => 'Another added licence undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                [
                    'action' => 'U',
                    'notes' => 'Updated licence undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                [
                    'action' => 'U',
                    'notes' => 'Another updated licence undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                [
                    'action' => 'D',
                    'notes' => 'Deleted licence undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ],
                [
                    'action' => 'D',
                    'notes' => 'Another deleted licence undertaking',
                    'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                    'attachedTo' => ['id' => Condition::ATTACHED_TO_LICENCE]
                ]
            ]
        ];
        $expectedOcConds = [
            'list' => [
                111 => [
                    [
                        'action' => 'A',
                        'notes' => 'Added oc condition',
                        'conditionType' => ['id' => Condition::TYPE_CONDITION],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 111]
                    ],
                    [
                        'action' => 'A',
                        'notes' => 'Another added oc condition',
                        'conditionType' => ['id' => Condition::TYPE_CONDITION],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 111]
                    ],
                    [
                        'action' => 'U',
                        'notes' => 'Updated oc condition',
                        'conditionType' => ['id' => Condition::TYPE_CONDITION],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 111]
                    ],
                    [
                        'action' => 'U',
                        'notes' => 'Another updated oc condition',
                        'conditionType' => ['id' => Condition::TYPE_CONDITION],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 111]
                    ],
                    [
                        'action' => 'D',
                        'notes' => 'Deleted oc condition',
                        'conditionType' => ['id' => Condition::TYPE_CONDITION],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 111]
                    ],
                    [
                        'action' => 'D',
                        'notes' => 'Another deleted oc condition',
                        'conditionType' => ['id' => Condition::TYPE_CONDITION],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 111]
                    ],
                ],
                222 => [
                    [
                        'action' => 'A',
                        'notes' => 'Another added oc condition',
                        'conditionType' => ['id' => Condition::TYPE_CONDITION],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 222]
                    ],
                    [
                        'action' => 'U',
                        'notes' => 'Another updated oc condition',
                        'conditionType' => ['id' => Condition::TYPE_CONDITION],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 222]
                    ],
                    [
                        'action' => 'D',
                        'notes' => 'Another deleted oc condition',
                        'conditionType' => ['id' => Condition::TYPE_CONDITION],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 222]
                    ],
                ]
            ]
        ];
        $expectedOcUnds = [
            'list' => [
                111 => [
                    [
                        'action' => 'A',
                        'notes' => 'Added oc undertaking',
                        'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 111]
                    ],
                    [
                        'action' => 'A',
                        'notes' => 'Another added oc undertaking',
                        'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 111]
                    ],
                    [
                        'action' => 'U',
                        'notes' => 'Updated oc undertaking',
                        'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 111]
                    ],
                    [
                        'action' => 'U',
                        'notes' => 'Another updated oc undertaking',
                        'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 111]
                    ],
                    [
                        'action' => 'D',
                        'notes' => 'Deleted oc undertaking',
                        'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 111]
                    ],
                    [
                        'action' => 'D',
                        'notes' => 'Another deleted oc undertaking',
                        'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 111]
                    ],
                ],
                222 => [
                    [
                        'action' => 'A',
                        'notes' => 'Another added oc undertaking',
                        'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 222]
                    ],
                    [
                        'action' => 'U',
                        'notes' => 'Another updated oc undertaking',
                        'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 222]
                    ],
                    [
                        'action' => 'D',
                        'notes' => 'Another deleted oc undertaking',
                        'conditionType' => ['id' => Condition::TYPE_UNDERTAKING],
                        'attachedTo' => ['id' => Condition::ATTACHED_TO_OPERATING_CENTRE],
                        'operatingCentre' => ['id' => 222]
                    ],
                ]
            ]
        ];

        [$licConds, $licUnds, $ocConds, $ocUnds] = $this->sut->splitUpConditionsAndUndertakings($data, false);

        $this->assertEquals($expectedLicConds, $licConds);
        $this->assertEquals($expectedLicUnds, $licUnds);
        $this->assertEquals($expectedOcConds, $ocConds);
        $this->assertEquals($expectedOcUnds, $ocUnds);
    }
}
