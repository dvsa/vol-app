<?php

declare(strict_types=1);

namespace CommonTest\Data\Mapper\Lva;

use Common\Data\Mapper\Lva\OperatingCentres;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\TranslationHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Common\Data\Mapper\Lva\OperatingCentres
 */
class OperatingCentresTest extends MockeryTestCase
{
    public const LOCATION = 'EXTERNAL';

    public const TRANSL = '_TRANSL_';

    /** @var  m\MockInterface | TranslationHelperService*/
    private $mockTranslator;

    /** @var  m\MockInterface | FlashMessengerHelperService */
    private $mockFlashMsg;

    #[\Override]
    protected function setUp(): void
    {
        $this->mockTranslator = m::mock(TranslationHelperService::class);
        $this->mockFlashMsg = m::mock(FlashMessengerHelperService::class);
    }

    /**
     * @dataProvider dpTestMapFromResult
     */
    public function testMapFromResult($result, $expected): void
    {
        $this->assertEquals($expected, OperatingCentres::mapFromResult($result));
    }

    /**
     * @return ((((int|string)[]|int|null|string)[]|int|null|string)[]|int|null|string)[][][]
     *
     * @psalm-return list{array{result: array{foo: 'bar', licence: array{enforcementArea: array{id: 123}}, totAuthHgvVehicles: null, totAuthLgvVehicles: 0, totAuthTrailers: 1, totCommunityLicences: 2}, expected: array{data: array{foo: 'bar', licence: array{enforcementArea: array{id: 123}}, totAuthHgvVehiclesFieldset: array{totAuthHgvVehicles: null}, totAuthLgvVehiclesFieldset: array{totAuthLgvVehicles: 0}, totAuthTrailersFieldset: array{totAuthTrailers: 1}, totCommunityLicencesFieldset: array{totCommunityLicences: 2}}, dataTrafficArea: array{trafficArea: null, enforcementArea: 123}}}, list{array{foo: 'bar', enforcementArea: array{id: 123}, licence: array{trafficArea: array{id: 'X'}}, totAuthHgvVehicles: null, totAuthLgvVehicles: 0, totAuthTrailers: 1, totCommunityLicences: 2}, array{data: array{foo: 'bar', enforcementArea: array{id: 123}, licence: array{trafficArea: array{id: 'X'}}, totAuthHgvVehiclesFieldset: array{totAuthHgvVehicles: null}, totAuthLgvVehiclesFieldset: array{totAuthLgvVehicles: 0}, totAuthTrailersFieldset: array{totAuthTrailers: 1}, totCommunityLicencesFieldset: array{totCommunityLicences: 2}}, dataTrafficArea: array{trafficArea: 'X', enforcementArea: 123}}}, list{array{foo: 'bar', enforcementArea: array{id: 123}, trafficArea: array{id: 'X'}, totAuthHgvVehicles: null, totAuthLgvVehicles: 0, totAuthTrailers: 1, totCommunityLicences: 2}, array{data: array{foo: 'bar', enforcementArea: array{id: 123}, trafficArea: array{id: 'X'}, totAuthHgvVehiclesFieldset: array{totAuthHgvVehicles: null}, totAuthLgvVehiclesFieldset: array{totAuthLgvVehicles: 0}, totAuthTrailersFieldset: array{totAuthTrailers: 1}, totCommunityLicencesFieldset: array{totCommunityLicences: 2}}, dataTrafficArea: array{trafficArea: 'X', enforcementArea: 123}}}}
     */
    public function dpTestMapFromResult(): array
    {
        return [
            [
                'result' => [
                    'foo' => 'bar',
                    'licence' => [
                        'enforcementArea' => [
                            'id' => 123
                        ]
                    ],
                    'totAuthHgvVehicles' => null,
                    'totAuthLgvVehicles' => 0,
                    'totAuthTrailers' => 1,
                    'totCommunityLicences' => 2,
                ],
                'expected' => [
                    'data' => [
                        'foo' => 'bar',
                        'licence' => [
                            'enforcementArea' => [
                                'id' => 123
                            ]
                        ],
                        'totAuthHgvVehiclesFieldset' => ['totAuthHgvVehicles' => null],
                        'totAuthLgvVehiclesFieldset' => ['totAuthLgvVehicles' => 0],
                        'totAuthTrailersFieldset' => ['totAuthTrailers' => 1],
                        'totCommunityLicencesFieldset' => ['totCommunityLicences' => 2],
                    ],
                    'dataTrafficArea' => [
                        'trafficArea' => null,
                        'enforcementArea' => 123
                    ]
                ]
            ],
            [
                [
                    'foo' => 'bar',
                    'enforcementArea' => [
                        'id' => 123
                    ],
                    'licence' => [
                        'trafficArea' => ['id' => 'X']
                    ],
                    'totAuthHgvVehicles' => null,
                    'totAuthLgvVehicles' => 0,
                    'totAuthTrailers' => 1,
                    'totCommunityLicences' => 2,
                ],
                [
                    'data' => [
                        'foo' => 'bar',
                        'enforcementArea' => [
                            'id' => 123
                        ],
                        'licence' => [
                            'trafficArea' => ['id' => 'X']
                        ],
                        'totAuthHgvVehiclesFieldset' => ['totAuthHgvVehicles' => null],
                        'totAuthLgvVehiclesFieldset' => ['totAuthLgvVehicles' => 0],
                        'totAuthTrailersFieldset' => ['totAuthTrailers' => 1],
                        'totCommunityLicencesFieldset' => ['totCommunityLicences' => 2],
                    ],
                    'dataTrafficArea' => [
                        'trafficArea' => 'X',
                        'enforcementArea' => 123
                    ]
                ]
            ],
            [
                [
                    'foo' => 'bar',
                    'enforcementArea' => [
                        'id' => 123
                    ],
                    'trafficArea' => ['id' => 'X'],
                    'totAuthHgvVehicles' => null,
                    'totAuthLgvVehicles' => 0,
                    'totAuthTrailers' => 1,
                    'totCommunityLicences' => 2,
                ],
                [
                    'data' => [
                        'foo' => 'bar',
                        'enforcementArea' => [
                            'id' => 123
                        ],
                        'trafficArea' => ['id' => 'X'],
                        'totAuthHgvVehiclesFieldset' => ['totAuthHgvVehicles' => null],
                        'totAuthLgvVehiclesFieldset' => ['totAuthLgvVehicles' => 0],
                        'totAuthTrailersFieldset' => ['totAuthTrailers' => 1],
                        'totCommunityLicencesFieldset' => ['totCommunityLicences' => 2],
                    ],
                    'dataTrafficArea' => [
                        'trafficArea' => 'X',
                        'enforcementArea' => 123
                    ]
                ]
            ]
        ];
    }

    /**
     * @dataProvider dpMapFromForm
     */
    public function testMapFromForm($formData, $expected): void
    {
        $this->assertEquals($expected, OperatingCentres::mapFromForm($formData));
    }

    /**
     * @return (((int|null)[]|string)[]|int|null|string)[][][]
     *
     * @psalm-return array{'all fieldsets included': array{formData: array{data: array{foo: 'bar', totAuthHgvVehiclesFieldset: array{totAuthHgvVehicles: null}, totAuthLgvVehiclesFieldset: array{totAuthLgvVehicles: 0}, totAuthTrailersFieldset: array{totAuthTrailers: 1}, totCommunityLicencesFieldset: array{totCommunityLicences: 2}}, dataTrafficArea: array{bar: 'cake'}}, expected: array{foo: 'bar', bar: 'cake', totAuthHgvVehicles: null, totAuthLgvVehicles: 0, totAuthTrailers: 1, totCommunityLicences: 2}}, 'all fieldsets removed': array{formData: array{data: array{foo: 'bar'}}, expected: array{foo: 'bar'}}}
     */
    public function dpMapFromForm(): array
    {
        return [
            'all fieldsets included' => [
                'formData' => [
                    'data' => [
                        'foo' => 'bar',
                        'totAuthHgvVehiclesFieldset' => ['totAuthHgvVehicles' => null],
                        'totAuthLgvVehiclesFieldset' => ['totAuthLgvVehicles' => 0],
                        'totAuthTrailersFieldset' => ['totAuthTrailers' => 1],
                        'totCommunityLicencesFieldset' => ['totCommunityLicences' => 2],
                    ],
                    'dataTrafficArea' => [
                        'bar' => 'cake'
                    ]
                ],
                'expected' => [
                    'foo' => 'bar',
                    'bar' => 'cake',
                    'totAuthHgvVehicles' => null,
                    'totAuthLgvVehicles' => 0,
                    'totAuthTrailers' => 1,
                    'totCommunityLicences' => 2,
                ],
            ],
            'all fieldsets removed' => [
                'formData' => [
                    'data' => [
                        'foo' => 'bar',
                    ],
                ],
                'expected' => [
                    'foo' => 'bar',
                ],
            ],
        ];
    }

    public function testMapFormErrors(): void
    {
        $expectedMessages = [
            'data' => [
                'totCommunityLicencesFieldset' => [
                    'totCommunityLicences' => [
                        'bar1'
                    ],
                ],
                'totAuthHgvVehiclesFieldset' => [
                    'totAuthHgvVehicles' => [
                        'bar2'
                    ],
                ],
                'totAuthLgvVehiclesFieldset' => [
                    'totAuthLgvVehicles' => [
                        'bar3'
                    ],
                ],
                'totAuthTrailersFieldset' => [
                    'totAuthTrailers' => [
                        'bar4'
                    ],
                ],
            ],
            'table' => [
                'table' => [
                    'bar7'
                ]
            ],
            'dataTrafficArea' => [
                'enforcementArea' => [
                    'bar8'
                ]
            ]
        ];

        $errors = [
            'totCommunityLicences' => [
                'foo' => 'bar1'
            ],
            'totAuthHgvVehicles' => [
                'foo' => 'bar2'
            ],
            'totAuthLgvVehicles' => [
                'foo' => 'bar3'
            ],
            'totAuthTrailers' => [
                'foo' => 'bar4'
            ],
            'operatingCentres' => [
                'foo' => 'bar7'
            ],
            'enforcementArea' => [
                'foo' => 'bar8'
            ],
            'detach_error' => 'unit_ERR_MSG',
        ];

        $form = m::mock(\Laminas\Form\Form::class);
        $form->shouldReceive('setMessages')->once()->with($expectedMessages);

        $this->mockFlashMsg->shouldReceive('addCurrentErrorMessage')->once()->with('unit_ERR_MSG');

        OperatingCentres::mapFormErrors($form, $errors, $this->mockFlashMsg, $this->mockTranslator, self::LOCATION);
    }

    public function testMapApiErrors(): void
    {
        $this->mockTranslator
            ->shouldReceive('translateReplace')
            ->andReturnUsing(
                static function ($key, $args) {
                    static::assertEquals(key($args) . '_' . self::LOCATION, $key);
                    return self::TRANSL . current($args);
                }
            );

        $errors = [
            'detach_error' => 'unit_DETACH_ERR_MSG',
            'fieldset' => [
                'field' => 'unit_FLD_ERR_MSG',
            ],
            'trafficArea' => [
                ['CODE' => 'MESSAGE'],
                ['ERR_TA_GOODS' => 'unit_TA_GOODS_msg'],
                ['ERR_TA_PSV' => 'unit_TA_PSV_msg'],
                ['ERR_TA_PSV_SR' => 'unit_TA_PSV_RS_msg'],
            ],
        ];

        $this->mockFlashMsg
            ->shouldReceive('addCurrentErrorMessage')->once()->with('unit_FLD_ERR_MSG')
            ->shouldReceive('addCurrentErrorMessage')->once()->with('unit_DETACH_ERR_MSG')
            ->shouldReceive('addCurrentErrorMessage')->once()->with('MESSAGE')
            ->shouldReceive('addCurrentErrorMessage')->once()->with(self::TRANSL . 'unit_TA_GOODS_msg')
            ->shouldReceive('addCurrentErrorMessage')->once()->with(self::TRANSL . 'unit_TA_PSV_msg')
            ->shouldReceive('addCurrentErrorMessage')->once()->with(self::TRANSL . 'unit_TA_PSV_RS_msg');

        OperatingCentres::mapApiErrors(self::LOCATION, $errors, $this->mockFlashMsg, $this->mockTranslator);
    }
}
