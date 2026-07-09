<?php

declare(strict_types=1);

/**
 * Application Vehicles Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\ApplicationVehiclesReviewService;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Application Vehicles Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class ApplicationVehiclesReviewServiceTest extends MockeryTestCase
{
    protected $sut;

    #[\Override]
    public function setUp(): void
    {
        $mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($mockTranslator);

        $this->sut = new ApplicationVehiclesReviewService($abstractReviewServiceServices);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('providerGetConfigFromData')]
    public function testGetConfigFromData(mixed $data, mixed $expected): void
    {
        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }

    public static function providerGetConfigFromData(): \Iterator
    {
        yield [
            [
                'hasEnteredReg' => 'N'
            ],
            [
                'subSections' => [
                    [
                        'mainItems' => [
                            [
                                'multiItems' => [
                                    [
                                        [
                                            'label' => 'application-review-vehicles-hasEnteredReg',
                                            'value' => 'No'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        yield [
            [
                'hasEnteredReg' => 'Y',
                'licenceVehicles' => [
                    [
                        'vehicle' => [
                            'vrm' => 'AB12QWE',
                            'platedWeight' => '1000'
                        ]
                    ],
                    [
                        'vehicle' => [
                            'vrm' => 'AB13QWE',
                            'platedWeight' => '10000'
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
                                            'label' => 'application-review-vehicles-hasEnteredReg',
                                            'value' => 'Yes'
                                        ]
                                    ],
                                    [
                                        [
                                            'label' => 'application-review-vehicles-vrm',
                                            'value' => 'AB12QWE'
                                        ],
                                        [
                                            'label' => 'application-review-vehicles-weight',
                                            'value' => '1,000 kg'
                                        ]
                                    ],
                                    [
                                        [
                                            'label' => 'application-review-vehicles-vrm',
                                            'value' => 'AB13QWE'
                                        ],
                                        [
                                            'label' => 'application-review-vehicles-weight',
                                            'value' => '10,000 kg'
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
