<?php

declare(strict_types=1);

/**
 * Application Taxi Phv Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\ApplicationTaxiPhvReviewService;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Application Taxi Phv Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationTaxiPhvReviewServiceTest extends MockeryTestCase
{
    protected $sut;

    public function setUp(): void
    {
        $mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->sut = new ApplicationTaxiPhvReviewService($abstractReviewServiceServices);
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
                    'licence' => [
                        'privateHireLicences' => [
                            [
                                'privateHireLicenceNo' => '12345678',
                                'contactDetails' => [
                                    'description' => 'Footown council',
                                    'address' => [
                                        'addressLine1' => '123',
                                        'addressLine2' => 'Foo street',
                                        'town' => 'Footown'
                                    ]
                                ]
                            ],
                            [
                                'privateHireLicenceNo' => '87659865',
                                'contactDetails' => [
                                    'description' => 'Footown council',
                                    'address' => [
                                        'addressLine1' => '123',
                                        'addressLine2' => 'Foo street',
                                        'town' => 'Footown'
                                    ]
                                ]
                            ],
                        ],
                        'trafficArea' => [
                            'name' => 'T A Name'
                        ]
                    ]
                ],
                [
                    'subSections' => [
                        [
                            'title' => 'application-review-taxi-phv-title',
                            'mainItems' => [
                                [
                                    'header' => '12345678',
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'application-review-taxi-phv-licence-number',
                                                'value' => '12345678'
                                            ],
                                            [
                                                'label' => 'application-review-taxi-phv-council-name',
                                                'value' => 'Footown council'
                                            ],
                                            [
                                                'label' => 'application-review-taxi-phv-address',
                                                'value' => '123, Foo street, Footown'
                                            ]
                                        ]
                                    ]
                                ],
                                [
                                    'header' => '87659865',
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'application-review-taxi-phv-licence-number',
                                                'value' => '87659865'
                                            ],
                                            [
                                                'label' => 'application-review-taxi-phv-council-name',
                                                'value' => 'Footown council'
                                            ],
                                            [
                                                'label' => 'application-review-taxi-phv-address',
                                                'value' => '123, Foo street, Footown'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'title' => 'application-review-taxi-phv-traffic-area-title',
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'application-review-taxi-phv-traffic-area',
                                                'value' => 'T A Name'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
    }
}
