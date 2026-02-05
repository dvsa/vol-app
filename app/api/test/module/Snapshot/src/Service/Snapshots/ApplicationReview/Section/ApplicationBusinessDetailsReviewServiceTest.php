<?php

declare(strict_types=1);

/**
 * Application Business Details Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\ApplicationBusinessDetailsReviewService;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Application Business Details Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationBusinessDetailsReviewServiceTest extends MockeryTestCase
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

        $this->sut = new ApplicationBusinessDetailsReviewService($abstractReviewServiceServices);
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
            'Sole Trader (Without trading names)' => [
                [
                    'licence' => [
                        'companySubsidiaries' => [],
                        'tradingNames' => [],
                        'organisation' => [
                            'type' => [
                                'id' => Organisation::ORG_TYPE_SOLE_TRADER
                            ],
                            'natureOfBusiness' => 'Foobar',
                        ]
                    ]
                ],
                [
                    'subSections' => [
                        [
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        null,
                                        [
                                            [
                                                'label' => 'application-review-business-details-trading-names',
                                                'value' => 'review-none-added-translated'
                                            ]
                                        ],
                                        [
                                            [
                                                'label' => 'application-review-business-details-nature-of-business',
                                                'value' => 'Foobar'
                                            ],
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'Sole Trader (With trading names)' => [
                [
                    'licence' => [
                        'companySubsidiaries' => [],
                        'tradingNames' => [
                            [
                                'name' => 'My foo'
                            ],
                            [
                                'name' => 'My bar'
                            ]
                        ],
                        'organisation' => [
                            'type' => [
                                'id' => Organisation::ORG_TYPE_SOLE_TRADER
                            ],
                            'natureOfBusiness' => 'Foobar',
                        ]
                    ]
                ],
                [
                    'subSections' => [
                        [
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        null,
                                        [
                                            [
                                                'label' => 'application-review-business-details-trading-names',
                                                'value' => 'My foo'
                                            ],
                                            [
                                                // Only the first TN has a label
                                                'label' => '',
                                                'value' => 'My bar'
                                            ]
                                        ],
                                        [
                                            [
                                                'label' => 'application-review-business-details-nature-of-business',
                                                'value' => 'Foobar'
                                            ],
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'Partnership (Without trading names)' => [
                [
                    'licence' => [
                        'companySubsidiaries' => [],
                        'tradingNames' => [],
                        'organisation' => [
                            'type' => [
                                'id' => Organisation::ORG_TYPE_PARTNERSHIP
                            ],
                            'name' => 'Our company',
                            'natureOfBusiness' => 'Foobar',
                        ]
                    ]
                ],
                [
                    'subSections' => [
                        [
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'application-review-business-details-partnership-name',
                                                'value' => 'Our company'
                                            ]
                                        ],
                                        [
                                            [
                                                'label' => 'application-review-business-details-trading-names',
                                                'value' => 'review-none-added-translated'
                                            ]
                                        ],
                                        [
                                            [
                                                'label' => 'application-review-business-details-nature-of-business',
                                                'value' => 'Foobar'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'Partnership (With trading names)' => [
                [
                    'licence' => [
                        'companySubsidiaries' => [],
                        'tradingNames' => [
                            [
                                'name' => 'Our foo'
                            ],
                            [
                                'name' => 'Our bar'
                            ]
                        ],
                        'organisation' => [
                            'type' => [
                                'id' => Organisation::ORG_TYPE_PARTNERSHIP
                            ],
                            'name' => 'Our company',
                            'natureOfBusiness' => 'Foobar',
                        ]
                    ]
                ],
                [
                    'subSections' => [
                        [
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'application-review-business-details-partnership-name',
                                                'value' => 'Our company'
                                            ]
                                        ],
                                        [
                                            [
                                                'label' => 'application-review-business-details-trading-names',
                                                'value' => 'Our foo'
                                            ],
                                            [
                                                // Only the first TN has a label
                                                'label' => '',
                                                'value' => 'Our bar'
                                            ]
                                        ],
                                        [
                                            [
                                                'label' => 'application-review-business-details-nature-of-business',
                                                'value' => 'Foobar'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'Registered Company (No trading names or subsidiaries)' => [
                [
                    'licence' => [
                        'companySubsidiaries' => [],
                        'tradingNames' => [],
                        'organisation' => [
                            'type' => [
                                'id' => Organisation::ORG_TYPE_REGISTERED_COMPANY
                            ],
                            'companyOrLlpNo' => '12345678',
                            'name' => 'Foo Ltd',
                            'natureOfBusiness' => 'Foobar',
                            'contactDetails' => [
                                'address' => [
                                    'addressLine1' => '123',
                                    'addressLine2' => 'Foo lane',
                                    'town' => 'Bartown',
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'subSections' => [
                        [
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'application-review-business-details-company-no',
                                                'value' => '12345678'
                                            ],
                                            [
                                                'label' => 'application-review-business-details-company-name',
                                                'value' => 'Foo Ltd'
                                            ]
                                        ],
                                        [
                                            [
                                                'label' => 'application-review-business-details-trading-names',
                                                'value' => 'review-none-added-translated'
                                            ]
                                        ],
                                        [
                                            [
                                                'label' => 'application-review-business-details-nature-of-business',
                                                'value' => 'Foobar'
                                            ]
                                        ],
                                        [
                                            [
                                                'label' => 'application-review-business-details-registered-address',
                                                'value' => '123, Foo lane, Bartown'
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'header' => 'application-review-business-details-subsidiary-company-header',
                                    'freetext' => 'review-none-added-translated'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'Registered Company (With trading names and subsidiaries)' => [
                [
                    'licence' => [
                        'companySubsidiaries' => [
                            [
                                'name' => 'Foosub Ltd',
                                'companyNo' => '23456789'
                            ],
                            [
                                'name' => 'Barsub Ltd',
                                'companyNo' => '34567891'
                            ]
                        ],
                        'tradingNames' => [
                            [
                                'name' => 'Foobar Ltd'
                            ],
                            [
                                'name' => 'Barfoo Ltd'
                            ]
                        ],
                        'organisation' => [
                            'type' => [
                                'id' => Organisation::ORG_TYPE_REGISTERED_COMPANY
                            ],
                            'companyOrLlpNo' => '12345678',
                            'name' => 'Foo Ltd',
                            'natureOfBusiness' => 'Foobar',
                            'contactDetails' => [
                                'address' => [
                                    'addressLine1' => '123',
                                    'addressLine2' => 'Foo lane',
                                    'town' => 'Bartown',
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'subSections' => [
                        [
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'application-review-business-details-company-no',
                                                'value' => '12345678'
                                            ],
                                            [
                                                'label' => 'application-review-business-details-company-name',
                                                'value' => 'Foo Ltd'
                                            ]
                                        ],
                                        [
                                            [
                                                'label' => 'application-review-business-details-trading-names',
                                                'value' => 'Foobar Ltd'
                                            ],
                                            [
                                                // Only the first TN has a label
                                                'label' => '',
                                                'value' => 'Barfoo Ltd'
                                            ]
                                        ],
                                        [
                                            [
                                                'label' => 'application-review-business-details-nature-of-business',
                                                'value' => 'Foobar'
                                            ]
                                        ],
                                        [
                                            [
                                                'label' => 'application-review-business-details-registered-address',
                                                'value' => '123, Foo lane, Bartown'
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'header' => 'application-review-business-details-subsidiary-company-header',
                                    'multiItems' => [
                                        [
                                            [
                                                'label'
                                                    => 'application-review-business-details-subsidiary-company-name',
                                                'value' => 'Foosub Ltd'
                                            ],
                                            [
                                                'label' => 'application-review-business-details-subsidiary-company-no',
                                                'value' => '23456789'
                                            ]
                                        ],
                                        [
                                            [
                                                'label'
                                                    => 'application-review-business-details-subsidiary-company-name',
                                                'value' => 'Barsub Ltd'
                                            ],
                                            [
                                                'label' => 'application-review-business-details-subsidiary-company-no',
                                                'value' => '34567891'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            // Should be identical to Ltd
            'LLP (No trading names or subsidiaries)' => [
                [
                    'licence' => [
                        'companySubsidiaries' => [],
                        'tradingNames' => [],
                        'organisation' => [
                            'type' => [
                                'id' => Organisation::ORG_TYPE_LLP
                            ],
                            'companyOrLlpNo' => '12345678',
                            'name' => 'Foo Ltd',
                            'natureOfBusiness' => 'Foobar',
                            'contactDetails' => [
                                'address' => [
                                    'addressLine1' => '123',
                                    'addressLine2' => 'Foo lane',
                                    'town' => 'Bartown',
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'subSections' => [
                        [
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'application-review-business-details-company-no',
                                                'value' => '12345678'
                                            ],
                                            [
                                                'label' => 'application-review-business-details-company-name',
                                                'value' => 'Foo Ltd'
                                            ]
                                        ],
                                        [
                                            [
                                                'label' => 'application-review-business-details-trading-names',
                                                'value' => 'review-none-added-translated'
                                            ]
                                        ],
                                        [
                                            [
                                                'label' => 'application-review-business-details-nature-of-business',
                                                'value' => 'Foobar'
                                            ]
                                        ],
                                        [
                                            [
                                                'label' => 'application-review-business-details-registered-address',
                                                'value' => '123, Foo lane, Bartown'
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'header' => 'application-review-business-details-subsidiary-company-header',
                                    'freetext' => 'review-none-added-translated'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'LLP (With trading names and subsidiaries)' => [
                [
                    'licence' => [
                        'companySubsidiaries' => [
                            [
                                'name' => 'Foosub Ltd',
                                'companyNo' => '23456789'
                            ],
                            [
                                'name' => 'Barsub Ltd',
                                'companyNo' => '34567891'
                            ]
                        ],
                        'tradingNames' => [
                            [
                                'name' => 'Foobar Ltd'
                            ],
                            [
                                'name' => 'Barfoo Ltd'
                            ]
                        ],
                        'organisation' => [
                            'type' => [
                                'id' => Organisation::ORG_TYPE_LLP
                            ],
                            'companyOrLlpNo' => '12345678',
                            'name' => 'Foo Ltd',
                            'natureOfBusiness' => 'Foobar',
                            'contactDetails' => [
                                'address' => [
                                    'addressLine1' => '123',
                                    'addressLine2' => 'Foo lane',
                                    'town' => 'Bartown',
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'subSections' => [
                        [
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'application-review-business-details-company-no',
                                                'value' => '12345678'
                                            ],
                                            [
                                                'label' => 'application-review-business-details-company-name',
                                                'value' => 'Foo Ltd'
                                            ]
                                        ],
                                        [
                                            [
                                                'label' => 'application-review-business-details-trading-names',
                                                'value' => 'Foobar Ltd'
                                            ],
                                            [
                                                // Only the first TN has a label
                                                'label' => '',
                                                'value' => 'Barfoo Ltd'
                                            ]
                                        ],
                                        [
                                            [
                                                'label' => 'application-review-business-details-nature-of-business',
                                                'value' => 'Foobar'
                                            ]
                                        ],
                                        [
                                            [
                                                'label' => 'application-review-business-details-registered-address',
                                                'value' => '123, Foo lane, Bartown'
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'header' => 'application-review-business-details-subsidiary-company-header',
                                    'multiItems' => [
                                        [
                                            [
                                                'label'
                                                    => 'application-review-business-details-subsidiary-company-name',
                                                'value' => 'Foosub Ltd'
                                            ],
                                            [
                                                'label' => 'application-review-business-details-subsidiary-company-no',
                                                'value' => '23456789'
                                            ]
                                        ],
                                        [
                                            [
                                                'label'
                                                    => 'application-review-business-details-subsidiary-company-name',
                                                'value' => 'Barsub Ltd'
                                            ],
                                            [
                                                'label' => 'application-review-business-details-subsidiary-company-no',
                                                'value' => '34567891'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'Other' => [
                [
                    'licence' => [
                        'companySubsidiaries' => [],
                        'tradingNames' => [],
                        'organisation' => [
                            'type' => [
                                'id' => Organisation::ORG_TYPE_OTHER
                            ],
                            'name' => 'My other business',
                            'natureOfBusiness' => 'Foobar',
                        ]
                    ]
                ],
                [
                    'subSections' => [
                        [
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'application-review-business-details-organisation-name',
                                                'value' => 'My other business'
                                            ]
                                        ],
                                        null,
                                        [
                                            [
                                                'label' => 'application-review-business-details-nature-of-business',
                                                'value' => 'Foobar'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
        ];
    }
}
