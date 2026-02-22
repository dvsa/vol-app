<?php

declare(strict_types=1);

/**
 * Application Financial History Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\ApplicationFinancialHistoryReviewService;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Application Financial History Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationFinancialHistoryReviewServiceTest extends MockeryTestCase
{
    protected $sut;

    /** @var TranslatorInterface */
    protected $mockTranslator;

    public function setUp(): void
    {
        $this->mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($this->mockTranslator);

        $this->sut = new ApplicationFinancialHistoryReviewService($abstractReviewServiceServices);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('providerGetConfigFromData')]
    public function testGetConfigFromData(mixed $data, mixed $expected): void
    {
        $this->mockTranslator->shouldReceive('translate')
            ->andReturnUsing(
                fn($string) => $string . '-translated'
            );

        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }

    public static function providerGetConfigFromData(): array
    {
        return [
            'Nos' => [
                [
                    'bankrupt' => 'N',
                    'liquidation' => 'N',
                    'receivership' => 'N',
                    'administration' => 'N',
                    'disqualified' => 'N',
                    'insolvencyDetails' => '',
                    'insolvencyConfirmation' => 'Y',
                    'variationType' => [
                        'id' => 'alksd'
                    ],
                ],
                [
                    'multiItems' => [
                        [
                            [
                                'label' => 'application-review-financial-history-bankrupt',
                                'value' => 'No'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-liquidation',
                                'value' => 'No'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-receivership',
                                'value' => 'No'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-administration',
                                'value' => 'No'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-disqualified',
                                'value' => 'No'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-insolvencyConfirmation',
                                'value' => 'Confirmed'
                            ]
                        ]
                    ]
                ]
            ],
            'Yeses without documents' => [
                [
                    'bankrupt' => 'Y',
                    'liquidation' => 'N',
                    'receivership' => 'N',
                    'administration' => 'N',
                    'disqualified' => 'N',
                    'insolvencyDetails' => 'Some text in here',
                    'insolvencyConfirmation' => 'Y',
                    'documents' => [],
                    'variationType' => [
                        'id' => 'uwieas'
                    ],
                ],
                [
                    'multiItems' => [
                        [
                            [
                                'label' => 'application-review-financial-history-bankrupt',
                                'value' => 'Yes'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-liquidation',
                                'value' => 'No'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-receivership',
                                'value' => 'No'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-administration',
                                'value' => 'No'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-disqualified',
                                'value' => 'No'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-insolvencyDetails',
                                'value' => 'Some text in here'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-evidence',
                                'noEscape' => true,
                                'value' => 'application-review-financial-history-evidence-send-translated'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-insolvencyConfirmation',
                                'value' => 'Confirmed'
                            ]
                        ]
                    ]
                ]
            ],
            'Yeses with documents' => [
                [
                    'bankrupt' => 'Y',
                    'liquidation' => 'N',
                    'receivership' => 'N',
                    'administration' => 'N',
                    'disqualified' => 'N',
                    'insolvencyDetails' => 'Some text in here',
                    'insolvencyConfirmation' => 'Y',
                    'variationType' => [
                        'id' => 'kdjasn'
                    ],

                    'documents' => [
                        [
                            'description' => 'evidence1',
                            'category' => [
                                'id' => Category::CATEGORY_LICENSING
                            ],
                            'subCategory' => [
                                'id' => Category::DOC_SUB_CATEGORY_LICENCE_INSOLVENCY_DOCUMENT_DIGITAL
                            ]
                        ],
                        [
                            'description' => 'evidence2',
                            'category' => [
                                'id' => Category::CATEGORY_LICENSING
                            ],
                            'subCategory' => [
                                'id' => Category::DOC_SUB_CATEGORY_LICENCE_INSOLVENCY_DOCUMENT_DIGITAL
                            ]
                        ],
                        [
                            'description' => 'ignore1',
                            'category' => [
                                'id' => 'foo'
                            ],
                            'subCategory' => [
                                'id' => Category::DOC_SUB_CATEGORY_LICENCE_INSOLVENCY_DOCUMENT_DIGITAL
                            ]
                        ],
                        [
                            'description' => 'ignore2',
                            'category' => [
                                'id' => Category::CATEGORY_LICENSING
                            ],
                            'subCategory' => [
                                'id' => 'bar'
                            ]
                        ]
                    ]
                ],
                [
                    'multiItems' => [
                        [
                            [
                                'label' => 'application-review-financial-history-bankrupt',
                                'value' => 'Yes'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-liquidation',
                                'value' => 'No'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-receivership',
                                'value' => 'No'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-administration',
                                'value' => 'No'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-disqualified',
                                'value' => 'No'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-insolvencyDetails',
                                'value' => 'Some text in here'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-evidence',
                                'noEscape' => true,
                                'value' => 'evidence1<br>evidence2'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-insolvencyConfirmation',
                                'value' => 'Confirmed'
                            ]
                        ]
                    ]
                ]
            ],
            'Director change Nos' => [
                [
                    'bankrupt' => 'N',
                    'liquidation' => 'N',
                    'receivership' => 'N',
                    'administration' => 'N',
                    'disqualified' => 'N',
                    'insolvencyDetails' => '',
                    'insolvencyConfirmation' => 'Y',
                    'variationType' => [
                        'id' => Application::VARIATION_TYPE_DIRECTOR_CHANGE
                    ],
                ],
                [
                    'multiItems' => [
                        [
                            [
                                'label' => 'application-review-financial-history-bankrupt',
                                'value' => 'No'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-liquidation',
                                'value' => 'No'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-receivership',
                                'value' => 'No'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-administration',
                                'value' => 'No'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-disqualified',
                                'value' => 'No'
                            ]
                        ]
                    ]
                ]
            ],
            'Director change yeses without documents' => [
                [
                    'bankrupt' => 'Y',
                    'liquidation' => 'N',
                    'receivership' => 'N',
                    'administration' => 'N',
                    'disqualified' => 'N',
                    'insolvencyDetails' => 'Some text in here',
                    'insolvencyConfirmation' => 'Y',
                    'documents' => [],
                    'variationType' => [
                        'id' => Application::VARIATION_TYPE_DIRECTOR_CHANGE
                    ],
                ],
                [
                    'multiItems' => [
                        [
                            [
                                'label' => 'application-review-financial-history-bankrupt',
                                'value' => 'Yes'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-liquidation',
                                'value' => 'No'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-receivership',
                                'value' => 'No'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-administration',
                                'value' => 'No'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-disqualified',
                                'value' => 'No'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-insolvencyDetails',
                                'value' => 'Some text in here'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-evidence',
                                'noEscape' => true,
                                'value' => 'application-review-financial-history-evidence-send-translated'
                            ]
                        ]
                    ]
                ]
            ],
            'Director change yeses with documents' => [
                [
                    'bankrupt' => 'Y',
                    'liquidation' => 'N',
                    'receivership' => 'N',
                    'administration' => 'N',
                    'disqualified' => 'N',
                    'insolvencyDetails' => 'Some text in here',
                    'insolvencyConfirmation' => 'Y',
                    'variationType' => [
                        'id' => Application::VARIATION_TYPE_DIRECTOR_CHANGE
                    ],

                    'documents' => [
                        [
                            'description' => 'evidence1',
                            'category' => [
                                'id' => Category::CATEGORY_LICENSING
                            ],
                            'subCategory' => [
                                'id' => Category::DOC_SUB_CATEGORY_LICENCE_INSOLVENCY_DOCUMENT_DIGITAL
                            ]
                        ],
                        [
                            'description' => 'evidence2',
                            'category' => [
                                'id' => Category::CATEGORY_LICENSING
                            ],
                            'subCategory' => [
                                'id' => Category::DOC_SUB_CATEGORY_LICENCE_INSOLVENCY_DOCUMENT_DIGITAL
                            ]
                        ],
                        [
                            'description' => 'ignore1',
                            'category' => [
                                'id' => 'foo'
                            ],
                            'subCategory' => [
                                'id' => Category::DOC_SUB_CATEGORY_LICENCE_INSOLVENCY_DOCUMENT_DIGITAL
                            ]
                        ],
                        [
                            'description' => 'ignore2',
                            'category' => [
                                'id' => Category::CATEGORY_LICENSING
                            ],
                            'subCategory' => [
                                'id' => 'bar'
                            ]
                        ]
                    ]
                ],
                [
                    'multiItems' => [
                        [
                            [
                                'label' => 'application-review-financial-history-bankrupt',
                                'value' => 'Yes'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-liquidation',
                                'value' => 'No'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-receivership',
                                'value' => 'No'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-administration',
                                'value' => 'No'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-disqualified',
                                'value' => 'No'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-insolvencyDetails',
                                'value' => 'Some text in here'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-financial-history-evidence',
                                'noEscape' => true,
                                'value' => 'evidence1<br>evidence2'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
