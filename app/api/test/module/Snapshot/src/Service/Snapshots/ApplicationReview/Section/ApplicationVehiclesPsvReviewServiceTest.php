<?php

declare(strict_types=1);

/**
 * Application Vehicles Psv Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\ApplicationVehiclesPsvReviewService;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\VehiclesPsvReviewService;
use Dvsa\Olcs\Api\Entity\Vehicle\Vehicle;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Application Vehicles Psv Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationVehiclesPsvReviewServiceTest extends MockeryTestCase
{
    protected $sut;

    /** @var TranslatorInterface */
    protected $mockTranslator;

    /** @var VehiclesPsvReviewService */
    private $mockVehiclesPsv;

    public function setUp(): void
    {
        $this->mockTranslator = m::mock(TranslatorInterface::class);

        $abstractReviewServiceServices = m::mock(AbstractReviewServiceServices::class);
        $abstractReviewServiceServices->shouldReceive('getTranslator')
            ->withNoArgs()
            ->andReturn($this->mockTranslator);

        $this->mockVehiclesPsv = m::mock(VehiclesPsvReviewService::class);

        $this->sut = new ApplicationVehiclesPsvReviewService(
            $abstractReviewServiceServices,
            $this->mockVehiclesPsv
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('providerGetConfigFromData')]
    public function testGetConfigFromData(mixed $data, mixed $expectedMainItems, mixed $expected): void
    {
        $this->mockTranslator->shouldReceive('translate')
            ->andReturnUsing(
                fn($string) => $string . '-translated'
            );

        $this->mockVehiclesPsv->shouldReceive('getConfigFromData')
            ->with($data, $expectedMainItems)
            ->andReturn('MAINITEMS');

        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }

    public static function providerGetConfigFromData(): array
    {
        return [
            [
                [
                    'hasEnteredReg' => 'N'
                ],
                [
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
                ],
                [
                    'subSections' => [
                        [
                            'mainItems' => 'MAINITEMS'
                        ]
                    ]
                ]
            ],
            [
                [
                    'hasEnteredReg' => 'Y',
                    'licenceVehicles' => [
                        [
                            'vehicle' => [
                                'vrm' => 'SM10QWE',
                                'makeModel' => 'Foo Bar',
                            ]
                        ],
                        [
                            'vehicle' => [
                                'vrm' => 'SM11QWE',
                                'makeModel' => 'Foo Bar',
                            ]
                        ],
                        [
                            'vehicle' => [
                                'vrm' => 'ME10QWE'
                            ]
                        ],
                        [
                            'vehicle' => [
                                'vrm' => 'ME11QWE'
                            ]
                        ],
                        [
                            'vehicle' => [
                                'vrm' => 'LG10QWE'
                            ]
                        ],
                        [
                            'vehicle' => [
                                'vrm' => 'LG11QWE'
                            ]
                        ]
                    ]
                ],
                [
                    [
                        'multiItems' => [
                            [
                                [
                                    'label' => 'application-review-vehicles-hasEnteredReg',
                                    'value' => 'Yes'
                                ]
                            ]
                        ]
                    ]
                ],
                [
                    'subSections' => [
                        [
                            'mainItems' => 'MAINITEMS'
                        ]
                    ]
                ]
            ]
        ];
    }
}
