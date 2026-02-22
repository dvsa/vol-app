<?php

declare(strict_types=1);

/**
 * Application Convictions Penalties Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\ApplicationConvictionsPenaltiesReviewService;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Application Convictions Penalties Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationConvictionsPenaltiesReviewServiceTest extends MockeryTestCase
{
    protected $sut;

    public function setUp(): void
    {
        $mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->sut = new ApplicationConvictionsPenaltiesReviewService($abstractReviewServiceServices);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('providerGetConfigFromData')]
    public function testGetConfigFromData(mixed $data, mixed $expected): void
    {
        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }

    public static function providerGetConfigFromData(): array
    {
        return [
            [
                [
                    'prevConviction' => 'N',
                    'convictionsConfirmation' => 'Y',
                    'variationType' => [
                        'id' => 'kdjasn'
                    ]
                ],
                [
                    'multiItems' => [
                        [
                            [
                                'label' => 'application-review-convictions-penalties-question',
                                'value' => 'No'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-convictions-penalties-confirmation',
                                'value' => 'Confirmed'
                            ]
                        ]
                    ]
                ]
            ],
            [
                [
                    'prevConviction' => 'Y',
                    'convictionsConfirmation' => 'Y',
                    'variationType' => [
                        'id' => 'kjknnk'
                    ],
                    'previousConvictions' => [
                        [
                            'forename' => 'Bob',
                            'familyName' => 'Smith',
                            'title' => [
                                'description' => 'Mr'
                            ],
                            'convictionDate' => '1989-08-23',
                            'categoryText' => 'Some crime',
                            'notes' => 'Details about crime',
                            'courtFpn' => 'Some court',
                            'penalty' => 'Slapped wrist'
                        ]
                    ]
                ],
                [
                    'subSections' => [
                        [
                            'mainItems' => [
                                [
                                    'header' => 'Bob Smith',
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'application-review-convictions-penalties-conviction-title',
                                                'value' => 'Mr'
                                            ],
                                            [
                                                'label'
                                                => 'application-review-convictions-penalties-conviction-forename',
                                                'value' => 'Bob'
                                            ],
                                            [
                                                'label'
                                                => 'application-review-convictions-penalties-conviction-familyName',
                                                'value' => 'Smith'
                                            ],
                                            [
                                                'label'
                                                => 'application-review-convictions-penalties-conviction-'
                                                    . 'convictionDate',
                                                'value' => '23 Aug 1989'
                                            ],
                                            [
                                                'label'
                                                => 'application-review-convictions-penalties-conviction-offence',
                                                'value' => 'Some crime'
                                            ],
                                            [
                                                'label'
                                                => 'application-review-convictions-penalties-conviction-offence-'
                                                    . 'details',
                                                'value' => 'Details about crime'
                                            ],
                                            [
                                                'label'
                                                => 'application-review-convictions-penalties-conviction-offence-'
                                                    . 'court',
                                                'value' => 'Some court'
                                            ],
                                            [
                                                'label'
                                                => 'application-review-convictions-penalties-conviction-offence-'
                                                    . 'penalty',
                                                'value' => 'Slapped wrist'
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'multiItems' => [
                                        [],
                                        [
                                            [
                                                'label' => 'application-review-convictions-penalties-confirmation',
                                                'value' => 'Confirmed'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            [
                [
                    'prevConviction' => 'N',
                    'convictionsConfirmation' => 'Y',
                    'variationType' => [
                        'id' => Application::VARIATION_TYPE_DIRECTOR_CHANGE
                    ]
                ],
                [
                    'multiItems' => [
                        [
                            [
                                'label' => 'application-review-convictions-penalties-question',
                                'value' => 'No'
                            ]
                        ],
                    ]
                ]
            ],
            [
                [
                    'prevConviction' => 'Y',
                    'convictionsConfirmation' => 'Y',
                    'variationType' => [
                        'id' => Application::VARIATION_TYPE_DIRECTOR_CHANGE
                    ],
                    'previousConvictions' => [
                        [
                            'forename' => 'Bob',
                            'familyName' => 'Smith',
                            'title' => [
                                'description' => 'Mr'
                            ],
                            'convictionDate' => '1989-08-23',
                            'categoryText' => 'Some crime',
                            'notes' => 'Details about crime',
                            'courtFpn' => 'Some court',
                            'penalty' => 'Slapped wrist'
                        ]
                    ]
                ],
                [
                    'subSections' => [
                        [
                            'mainItems' => [
                                [
                                    'header' => 'Bob Smith',
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'application-review-convictions-penalties-conviction-title',
                                                'value' => 'Mr'
                                            ],
                                            [
                                                'label'
                                                => 'application-review-convictions-penalties-conviction-forename',
                                                'value' => 'Bob'
                                            ],
                                            [
                                                'label'
                                                => 'application-review-convictions-penalties-conviction-familyName',
                                                'value' => 'Smith'
                                            ],
                                            [
                                                'label'
                                                => 'application-review-convictions-penalties-conviction-'
                                                    . 'convictionDate',
                                                'value' => '23 Aug 1989'
                                            ],
                                            [
                                                'label'
                                                => 'application-review-convictions-penalties-conviction-offence',
                                                'value' => 'Some crime'
                                            ],
                                            [
                                                'label'
                                                => 'application-review-convictions-penalties-conviction-offence-'
                                                    . 'details',
                                                'value' => 'Details about crime'
                                            ],
                                            [
                                                'label'
                                                => 'application-review-convictions-penalties-conviction-offence-'
                                                    . 'court',
                                                'value' => 'Some court'
                                            ],
                                            [
                                                'label'
                                                => 'application-review-convictions-penalties-conviction-offence-'
                                                    . 'penalty',
                                                'value' => 'Slapped wrist'
                                            ]
                                        ]
                                    ]
                                ],
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
