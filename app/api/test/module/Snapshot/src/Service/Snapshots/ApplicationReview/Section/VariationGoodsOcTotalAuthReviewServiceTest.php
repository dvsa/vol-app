<?php

declare(strict_types=1);

/**
 * Variation Goods Oc Total Auth Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\VariationGoodsOcTotalAuthReviewService;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Variation Goods Oc Total Auth Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationGoodsOcTotalAuthReviewServiceTest extends MockeryTestCase
{
    protected $sut;

    public function setUp(): void
    {
        $mockTranslator = m::mock(TranslatorInterface::class);
        $mockTranslator->shouldReceive('translate')
            ->with('review-value-decreased')
            ->andReturn('decreased from %s to %s')
            ->shouldReceive('translate')
            ->with('review-value-increased')
            ->andReturn('increased from %s to %s');

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->sut = new VariationGoodsOcTotalAuthReviewService($abstractReviewServiceServices);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGetConfigFromData')]
    public function testGetConfigFromData(mixed $data, mixed $expected): void
    {
        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }

    public static function dpGetConfigFromData(): array
    {
        return [
            'without changes' => [
                'data' => [
                    'licenceType' => ['id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL],
                    'totAuthVehicles' => 10,
                    'totAuthHgvVehicles' => 10,
                    'totAuthLgvVehicles' => null,
                    'totAuthTrailers' => 9,
                    'licence' => [
                        'totAuthVehicles' => 10,
                        'totAuthHgvVehicles' => 10,
                        'totAuthLgvVehicles' => null,
                        'totAuthTrailers' => 9,
                    ]
                ],
                'expected' => null,
            ],
            'without changes - eligible for LGV' => [
                'data' => [
                    'licenceType' => ['id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL],
                    'totAuthVehicles' => 10,
                    'totAuthHgvVehicles' => 7,
                    'totAuthLgvVehicles' => 3,
                    'totAuthTrailers' => 9,
                    'totCommunityLicences' => 5,
                    'licence' => [
                        'totAuthVehicles' => 10,
                        'totAuthHgvVehicles' => 7,
                        'totAuthLgvVehicles' => 3,
                        'totAuthTrailers' => 9,
                        'totCommunityLicences' => 5,
                    ]
                ],
                'expected' => null,
            ],
            'with changes' => [
                'data' => [
                    'licenceType' => ['id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL],
                    'totAuthVehicles' => 10,
                    'totAuthHgvVehicles' => 10,
                    'totAuthLgvVehicles' => null,
                    'totAuthTrailers' => 9,
                    'licence' => [
                        'totAuthVehicles' => 20,
                        'totAuthHgvVehicles' => 20,
                        'totAuthLgvVehicles' => null,
                        'totAuthTrailers' => 4,
                    ]
                ],
                'expected' => [
                    'header' => 'review-operating-centres-authorisation-title',
                    'multiItems' => [
                        [
                            [
                                'label' => 'review-operating-centres-authorisation-vehicles',
                                'value' => 'decreased from 20 to 10'
                            ],
                            [
                                'label' => 'review-operating-centres-authorisation-trailers',
                                'value' => 'increased from 4 to 9'
                            ]
                        ]
                    ]
                ],
            ],
            'with changes - eligible for LGV' => [
                'data' => [
                    'licenceType' => ['id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL],
                    'totAuthVehicles' => 10,
                    'totAuthHgvVehicles' => 7,
                    'totAuthLgvVehicles' => 3,
                    'totAuthTrailers' => 9,
                    'totCommunityLicences' => 5,
                    'licence' => [
                        'totAuthVehicles' => 20,
                        'totAuthHgvVehicles' => 20,
                        'totAuthLgvVehicles' => null,
                        'totAuthTrailers' => 4,
                        'totCommunityLicences' => 15,
                    ]
                ],
                'expected' => [
                    'header' => 'review-operating-centres-authorisation-title',
                    'multiItems' => [
                        [
                            [
                                'label' => 'review-operating-centres-authorisation-vehicles-hgv',
                                'value' => 'decreased from 20 to 7'
                            ],
                            [
                                'label' => 'review-operating-centres-authorisation-vehicles-lgv',
                                'value' => 'increased from 0 to 3'
                            ],
                            [
                                'label' => 'review-operating-centres-authorisation-trailers',
                                'value' => 'increased from 4 to 9'
                            ],
                            [
                                'label' => 'review-operating-centres-authorisation-community-licences',
                                'value' => 'decreased from 15 to 5'
                            ]
                        ]
                    ]
                ],
            ],
            'with changes to zero' => [
                'data' => [
                    'licenceType' => ['id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL],
                    'totAuthVehicles' => 20,
                    'totAuthHgvVehicles' => 20,
                    'totAuthLgvVehicles' => null,
                    'totAuthTrailers' => 0,
                    'licence' => [
                        'totAuthVehicles' => 20,
                        'totAuthHgvVehicles' => 20,
                        'totAuthLgvVehicles' => null,
                        'totAuthTrailers' => 4,
                    ]
                ],
                'expected' => [
                    'header' => 'review-operating-centres-authorisation-title',
                    'multiItems' => [
                        [
                            [
                                'label' => 'review-operating-centres-authorisation-trailers',
                                'value' => 'decreased from 4 to 0'
                            ]
                        ]
                    ]
                ],
            ],
            'with changes to zero - eligible for LGV' => [
                'data' => [
                    'licenceType' => ['id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL],
                    'totAuthVehicles' => 20,
                    'totAuthHgvVehicles' => 15,
                    'totAuthLgvVehicles' => 0,
                    'totAuthTrailers' => 0,
                    'totCommunityLicences' => 15,
                    'licence' => [
                        'totAuthVehicles' => 20,
                        'totAuthHgvVehicles' => 15,
                        'totAuthLgvVehicles' => 5,
                        'totAuthTrailers' => 4,
                        'totCommunityLicences' => 15,
                    ]
                ],
                'expected' => [
                    'header' => 'review-operating-centres-authorisation-title',
                    'multiItems' => [
                        [
                            [
                                'label' => 'review-operating-centres-authorisation-vehicles-lgv',
                                'value' => 'decreased from 5 to 0'
                            ],
                            [
                                'label' => 'review-operating-centres-authorisation-trailers',
                                'value' => 'decreased from 4 to 0'
                            ],
                        ]
                    ]
                ],
            ],
            'with changes from null to zero - eligible for LGV' => [
                'data' => [
                    'licenceType' => ['id' => Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL],
                    'totAuthVehicles' => 20,
                    'totAuthHgvVehicles' => 15,
                    'totAuthLgvVehicles' => 0,
                    'totAuthTrailers' => 0,
                    'totCommunityLicences' => 15,
                    'licence' => [
                        'totAuthVehicles' => 20,
                        'totAuthHgvVehicles' => 15,
                        'totAuthLgvVehicles' => null,
                        'totAuthTrailers' => 5,
                        'totCommunityLicences' => 15,
                    ]
                ],
                'expected' => [
                    'header' => 'review-operating-centres-authorisation-title',
                    'multiItems' => [
                        [
                            [
                                'label' => 'review-operating-centres-authorisation-trailers',
                                'value' => 'decreased from 5 to 0'
                            ],
                        ]
                    ]
                ],
            ],
        ];
    }
}
