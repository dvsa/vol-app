<?php

/**
 * Application Vehicles Declarations Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Snapshot\Service\Snapshots\ApplicationReview\Section;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\AbstractReviewServiceServices;
use Dvsa\Olcs\Snapshot\Service\Snapshots\ApplicationReview\Section\ApplicationVehiclesDeclarationsReviewService;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Application Vehicles Declarations Review Service Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationVehiclesDeclarationsReviewServiceTest extends MockeryTestCase
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

        $this->sut = new ApplicationVehiclesDeclarationsReviewService($abstractReviewServiceServices);
    }

    /**
     * @dataProvider providerGetConfigFromData
     */
    public function testGetConfigFromData($data, $expected)
    {
        $this->mockTranslator->shouldReceive('translate')
            ->andReturnUsing(
                fn($string) => $string . '-translated'
            );

        $this->assertEquals($expected, $this->sut->getConfigFromData($data));
    }

    public function providerGetConfigFromData()
    {
        return [
            // Non Scottish, Non restricted licence, with small vehicles, with novelty vehicles
            [
                [
                    'psvWhichVehicleSizes' => [
                        'id' => Application::PSV_VEHICLE_SIZE_BOTH,
                        'description' => 'PSV_DESC'
                    ],
                    'psvSmallVhlNotes' => "Foo\nBar",
                    'psvLimousines' => 'Y',
                    'psvOperateSmallVhl' => 'Y',
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL
                    ],
                    'licence' => [
                        'trafficArea' => [
                            'isScotland' => false
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
                                                'label' => 'application-review-vehicles-declarations-vs',
                                                'value' => 'PSV_DESC'
                                            ],
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'title' => 'application-review-vehicles-declarations-small-title',
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        '15b' => [
                                            [
                                                'label' => 'application-review-vehicles-declarations-15b1',
                                                'value' => 'Yes'
                                            ],
                                            [
                                                'label' => 'application-review-vehicles-declarations-15b2',
                                                'noEscape' => true,
                                                'value' => "Foo<br />\nBar"
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'title' => 'application-review-vehicles-declarations-novelty-title',
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        '15f' => [
                                            [
                                                'label' => 'application-review-vehicles-declarations-15f1',
                                                'value' => 'Yes'
                                            ]
                                        ],
                                        '15g' => [
                                            [
                                                'label' => 'application-review-vehicles-declarations-15g',
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
            // Non Scottish, Non restricted licence, with small vehicles, without novelty vehicles
            [
                [
                    'psvWhichVehicleSizes' => [
                        'id' => Application::PSV_VEHICLE_SIZE_SMALL,
                        'description' => 'PSV_DESC'
                    ],
                    'psvSmallVhlNotes' => "Foo\nBar",
                    'psvLimousines' => 'N',
                    'psvOperateSmallVhl' => 'Y',
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL
                    ],
                    'licence' => [
                        'trafficArea' => [
                            'isScotland' => false
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
                                                'label' => 'application-review-vehicles-declarations-vs',
                                                'value' => 'PSV_DESC'
                                            ],
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'title' => 'application-review-vehicles-declarations-small-title',
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        '15b' => [
                                            [
                                                'label' => 'application-review-vehicles-declarations-15b1',
                                                'value' => 'Yes'
                                            ],
                                            [
                                                'label' => 'application-review-vehicles-declarations-15b2',
                                                'noEscape' => true,
                                                'value' => "Foo<br />\nBar"
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'title' => 'application-review-vehicles-declarations-novelty-title',
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        '15f' => [
                                            [
                                                'label' => 'application-review-vehicles-declarations-15f1',
                                                'value' => 'No'
                                            ],
                                            [
                                                'label' => 'application-review-vehicles-declarations-15f2',
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
            // Non Scottish, Non restricted licence, with small vehicles, without novelty vehicles, with conditions
            // and undertakings
            [
                [
                    'psvWhichVehicleSizes' => [
                        'id' => Application::PSV_VEHICLE_SIZE_SMALL,
                        'description' => 'PSV_DESC'
                    ],
                    'psvSmallVhlConfirmation' => 'Y',
                    'psvSmallVhlNotes' => "Foo\nBar",
                    'psvLimousines' => 'N',
                    'psvOperateSmallVhl' => 'N',
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL
                    ],
                    'licence' => [
                        'trafficArea' => [
                            'isScotland' => false
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
                                                'label' => 'application-review-vehicles-declarations-vs',
                                                'value' => 'PSV_DESC'
                                            ],
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'title' => 'application-review-vehicles-declarations-small-title',
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        '15b' => [
                                            [
                                                'label' => 'application-review-vehicles-declarations-15b1',
                                                'value' => 'No'
                                            ],
                                        ],
                                        [
                                            [
                                                'label' => 'application-review-vehicles-declarations-15cd',
                                                'value' => 'Confirmed'
                                            ],
                                        ],
                                        [
                                            [
                                                'full-content' => 'markup-application_vehicle-safety_undertakings-'
                                                    . 'smallVehiclesUndertakingsScotland-translated',
                                            ]
                                        ],
                                        [
                                            [
                                                'full-content' => '<h4>Undertakings</h4>'
                                                    . 'markup-application_vehicle-safety_undertakings-'
                                                    . 'smallVehiclesUndertakings-translated'
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'title' => 'application-review-vehicles-declarations-novelty-title',
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        '15f' => [
                                            [
                                                'label' => 'application-review-vehicles-declarations-15f1',
                                                'value' => 'No'
                                            ],
                                            [
                                                'label' => 'application-review-vehicles-declarations-15f2',
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
            // Scottish, Non restricted licence, with small vehicles, without novelty vehicles, with conditions
            // and undertakings
            [
                [
                    'psvWhichVehicleSizes' => [
                        'id' => Application::PSV_VEHICLE_SIZE_SMALL,
                        'description' => 'PSV_DESC'
                    ],
                    'psvSmallVhlConfirmation' => 'Y',
                    'psvSmallVhlNotes' => "Foo\nBar",
                    'psvLimousines' => 'N',
                    'psvOperateSmallVhl' => 'Y',
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL
                    ],
                    'licence' => [
                        'trafficArea' => [
                            'isScotland' => true
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
                                                'label' => 'application-review-vehicles-declarations-vs',
                                                'value' => 'PSV_DESC'
                                            ],
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'title' => 'application-review-vehicles-declarations-small-title',
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'application-review-vehicles-declarations-15cd',
                                                'value' => 'Confirmed'
                                            ],
                                        ],
                                        [
                                            [
                                                'full-content' => 'markup-application_vehicle-safety_undertakings-'
                                                    . 'smallVehiclesUndertakingsScotland-translated',
                                            ]
                                        ],
                                        [
                                            [
                                                'full-content' => '<h4>Undertakings</h4>'
                                                    . 'markup-application_vehicle-safety_undertakings-'
                                                    . 'smallVehiclesUndertakings-translated'
                                            ]
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        [
                            'title' => 'application-review-vehicles-declarations-novelty-title',
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        '15f' => [
                                            [
                                                'label' => 'application-review-vehicles-declarations-15f1',
                                                'value' => 'No'
                                            ],
                                            [
                                                'label' => 'application-review-vehicles-declarations-15f2',
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
            // Scottish, Non restricted licence, without small vehicles, with large vehicles, without novelty vehicles
            [
                [
                    'psvWhichVehicleSizes' => [
                        'id' => Application::PSV_VEHICLE_SIZE_MEDIUM_LARGE,
                        'description' => 'PSV_DESC'
                    ],
                    'psvNoSmallVhlConfirmation' => 'Y',
                    'psvSmallVhlConfirmation' => 'Y',
                    'psvSmallVhlNotes' => "Foo\nBar",
                    'psvLimousines' => 'N',
                    'psvOperateSmallVhl' => 'Y',
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL
                    ],
                    'licence' => [
                        'trafficArea' => [
                            'isScotland' => true
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
                                                'label' => 'application-review-vehicles-declarations-vs',
                                                'value' => 'PSV_DESC'
                                            ],
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'title' => 'application-review-vehicles-declarations-medium-title',
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'application-review-vehicles-declarations-15e',
                                                'value' => 'Confirmed'
                                            ]
                                        ],
                                    ]
                                ]
                            ]
                        ],
                        [
                            'title' => 'application-review-vehicles-declarations-novelty-title',
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        '15f' => [
                                            [
                                                'label' => 'application-review-vehicles-declarations-15f1',
                                                'value' => 'No'
                                            ],
                                            [
                                                'label' => 'application-review-vehicles-declarations-15f2',
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
            // Scottish, Non restricted licence, without small vehicles, with medium vehicles, without novelty vehicles
            [
                [
                    'psvWhichVehicleSizes' => [
                        'id' => Application::PSV_VEHICLE_SIZE_MEDIUM_LARGE,
                        'description' => 'PSV_DESC'
                    ],
                    'psvNoSmallVhlConfirmation' => 'Y',
                    'psvSmallVhlConfirmation' => 'Y',
                    'psvSmallVhlNotes' => "Foo\nBar",
                    'psvLimousines' => 'N',
                    'psvOperateSmallVhl' => 'Y',
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL
                    ],
                    'licence' => [
                        'trafficArea' => [
                            'isScotland' => true
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
                                                'label' => 'application-review-vehicles-declarations-vs',
                                                'value' => 'PSV_DESC'
                                            ],
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'title' => 'application-review-vehicles-declarations-medium-title',
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'application-review-vehicles-declarations-15e',
                                                'value' => 'Confirmed'
                                            ]
                                        ],
                                    ]
                                ]
                            ]
                        ],
                        [
                            'title' => 'application-review-vehicles-declarations-novelty-title',
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        '15f' => [
                                            [
                                                'label' => 'application-review-vehicles-declarations-15f1',
                                                'value' => 'No'
                                            ],
                                            [
                                                'label' => 'application-review-vehicles-declarations-15f2',
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
            // Scottish, Non restricted licence, without small vehicles, with large + medium vehicles, without novelty
            // vehicles
            [
                [
                    'psvWhichVehicleSizes' => [
                        'id' => Application::PSV_VEHICLE_SIZE_MEDIUM_LARGE,
                        'description' => 'PSV_DESC'
                    ],
                    'psvNoSmallVhlConfirmation' => 'Y',
                    'psvSmallVhlConfirmation' => 'Y',
                    'psvSmallVhlNotes' => "Foo\nBar",
                    'psvLimousines' => 'N',
                    'psvOperateSmallVhl' => 'Y',
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL
                    ],
                    'licence' => [
                        'trafficArea' => [
                            'isScotland' => true
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
                                                'label' => 'application-review-vehicles-declarations-vs',
                                                'value' => 'PSV_DESC'
                                            ],
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'title' => 'application-review-vehicles-declarations-medium-title',
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'application-review-vehicles-declarations-15e',
                                                'value' => 'Confirmed'
                                            ]
                                        ],
                                    ]
                                ]
                            ]
                        ],
                        [
                            'title' => 'application-review-vehicles-declarations-novelty-title',
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        '15f' => [
                                            [
                                                'label' => 'application-review-vehicles-declarations-15f1',
                                                'value' => 'No'
                                            ],
                                            [
                                                'label' => 'application-review-vehicles-declarations-15f2',
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
            // Scottish, Restricted licence, without small vehicles, with large + medium vehicles, without novelty
            // vehicles
            [
                [
                    'psvWhichVehicleSizes' => [
                        'id' => Application::PSV_VEHICLE_SIZE_MEDIUM_LARGE,
                        'description' => 'PSV_DESC'
                    ],
                    'psvMediumVhlConfirmation' => 'Y',
                    'psvNoSmallVhlConfirmation' => 'Y',
                    'psvSmallVhlConfirmation' => 'Y',
                    'psvMediumVhlNotes' => "Foo\nBar",
                    'psvLimousines' => 'N',
                    'psvOperateSmallVhl' => 'Y',
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_RESTRICTED
                    ],
                    'licence' => [
                        'trafficArea' => [
                            'isScotland' => true
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
                                                'label' => 'application-review-vehicles-declarations-vs',
                                                'value' => 'PSV_DESC'
                                            ],
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        [
                            'title' => 'application-review-vehicles-declarations-medium-title',
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'application-review-vehicles-declarations-15e',
                                                'value' => 'Confirmed'
                                            ]
                                        ],
                                    ]
                                ]
                            ]
                        ],
                        [
                            'title' => 'application-review-vehicles-declarations-business-title',
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        [
                                            [
                                                'label' => 'application-review-vehicles-declarations-8b1',
                                                'value' => 'Confirmed'
                                            ]
                                        ],
                                        [
                                            [
                                                'label' => 'application-review-vehicles-declarations-8b2',
                                                'noEscape' => true,
                                                'value' => "Foo<br />\nBar"
                                            ]
                                        ],
                                    ]
                                ]
                            ]
                        ],
                        [
                            'title' => 'application-review-vehicles-declarations-novelty-title',
                            'mainItems' => [
                                [
                                    'multiItems' => [
                                        '15f' => [
                                            [
                                                'label' => 'application-review-vehicles-declarations-15f1',
                                                'value' => 'No'
                                            ],
                                            [
                                                'label' => 'application-review-vehicles-declarations-15f2',
                                                'value' => 'Confirmed'
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
