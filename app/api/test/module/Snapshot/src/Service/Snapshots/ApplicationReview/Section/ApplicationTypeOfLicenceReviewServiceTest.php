<?php

declare(strict_types=1);

/**
 * Application Type Of Licence Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\ApplicationTypeOfLicenceReviewService;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Application Type Of Licence Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationTypeOfLicenceReviewServiceTest extends MockeryTestCase
{
    protected $sut;

    public function setUp(): void
    {
        $mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->sut = new ApplicationTypeOfLicenceReviewService($abstractReviewServiceServices);
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
                    'niFlag' => 'N',
                    'isGoods' => true,
                    'goodsOrPsv' => [
                        'id' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE
                    ],
                    'lgvDeclarationConfirmation' => false,
                    'licenceType' => [
                        'description' => 'Standard National'
                    ],
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_HGV
                    ],
                ],
                [
                    'multiItems' => [
                        [
                            [
                                'label' => 'application-review-type-of-licence-operator-location',
                                'value' => 'Great Britain'
                            ],
                            [
                                'label' => 'application-review-type-of-licence-operator-type',
                                'value' => 'Goods'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-type-of-licence-licence-type',
                                'value' => 'Standard National'
                            ]
                        ]
                    ]
                ]
            ],
            [
                [
                    'niFlag' => 'N',
                    'isGoods' => false,
                    'goodsOrPsv' => [
                        'id' => Licence::LICENCE_CATEGORY_PSV
                    ],
                    'lgvDeclarationConfirmation' => false,
                    'licenceType' => [
                        'description' => 'Standard International'
                    ],
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_MIXED
                    ],
                ],
                [
                    'multiItems' => [
                        [
                            [
                                'label' => 'application-review-type-of-licence-operator-location',
                                'value' => 'Great Britain'
                            ],
                            [
                                'label' => 'application-review-type-of-licence-operator-type',
                                'value' => 'PSV'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-type-of-licence-licence-type',
                                'value' => 'Standard International'
                            ],
                            [
                                'label' => 'application-review-type-of-licence-vehicle-type',
                                'value' => 'No'
                            ]
                        ],
                    ]
                ]
            ],
            [
                [
                    'niFlag' => 'N',
                    'isGoods' => false,
                    'goodsOrPsv' => [
                        'id' => Licence::LICENCE_CATEGORY_GOODS_VEHICLE
                    ],
                    'lgvDeclarationConfirmation' => true,
                    'licenceType' => [
                        'description' => 'Standard International'
                    ],
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_LGV
                    ],
                ],
                [
                    'multiItems' => [
                        [
                            [
                                'label' => 'application-review-type-of-licence-operator-location',
                                'value' => 'Great Britain'
                            ],
                            [
                                'label' => 'application-review-type-of-licence-operator-type',
                                'value' => 'PSV'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-type-of-licence-licence-type',
                                'value' => 'Standard International'
                            ],
                            [
                                'label' => 'application-review-type-of-licence-vehicle-type',
                                'value' => 'Yes'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-type-of-licence-lgv-declaration-confirmation',
                                'value' => 'Confirmed'
                            ]
                        ],
                    ]
                ]
            ],
            [
                [
                    'niFlag' => 'Y',
                    'licenceType' => [
                        'description' => 'Standard National'
                    ],
                    'vehicleType' => [
                        'id' => RefData::APP_VEHICLE_TYPE_HGV
                    ],
                ],
                [
                    'multiItems' => [
                        [
                            [
                                'label' => 'application-review-type-of-licence-operator-location',
                                'value' => 'Northern Ireland'
                            ]
                        ],
                        [
                            [
                                'label' => 'application-review-type-of-licence-licence-type',
                                'value' => 'Standard National'
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
