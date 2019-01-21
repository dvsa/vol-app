<?php

namespace PermitsTest\Data\Mapper;

use Common\Form\Form;
use Permits\Controller\Config\DataSource\AvailableCountries as AvailableCountriesDataSource;
use Permits\Controller\Config\DataSource\IrhpApplication as IrhpApplicationDataSource;
use Permits\Data\Mapper\AvailableCountries;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;

/**
 * AvailableCountries test
 */
class AvailableCountriesTest extends TestCase
{
    /**
     * @dataProvider dpTestMapForFormOptions
     */
    public function testMapForFormOptions($data, $expected, $expectedValueOptions, $expectedValue)
    {
        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('get')
            ->with('fields')
            ->andReturnSelf()
            ->shouldReceive('get')
            ->with('countries')
            ->andReturnSelf()
            ->shouldReceive('setValueOptions')
            ->with($expectedValueOptions)
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setValue')
            ->with($expectedValue)
            ->once();

        self::assertEquals($expected, AvailableCountries::mapForFormOptions($data, $mockForm));
    }

    public function dpTestMapForFormOptions()
    {
        return [
            'empty list' => [
                'data' => [
                    AvailableCountriesDataSource::DATA_KEY => [
                        'countries' => []
                    ],
                    IrhpApplicationDataSource::DATA_KEY => [
                        'irhpPermitApplications' => []
                    ],
                ],
                'expected' => [
                    AvailableCountriesDataSource::DATA_KEY => [
                        'countries' => []
                    ],
                    IrhpApplicationDataSource::DATA_KEY => [
                        'irhpPermitApplications' => []
                    ],
                ],
                'expectedValueOptions' => [],
                'expectedValue' => [],
            ],
            'list with data - nothing already selected' => [
                'data' => [
                    AvailableCountriesDataSource::DATA_KEY => [
                        'countries' => [
                            [
                                'id' => 'NL',
                                'countryDesc' => 'name 1',
                            ],
                            [
                                'id' => 'FR',
                                'countryDesc' => 'name 2',
                            ],
                            [
                                'id' => 'DE',
                                'countryDesc' => 'name 3',
                            ],
                            [
                                'id' => 'PT',
                                'countryDesc' => 'name 4',
                            ],
                        ]
                    ],
                    IrhpApplicationDataSource::DATA_KEY => [
                        'irhpPermitApplications' => []
                    ],
                ],
                'expected' => [
                    AvailableCountriesDataSource::DATA_KEY => [
                        'countries' => [
                            [
                                'id' => 'NL',
                                'countryDesc' => 'name 1',
                            ],
                            [
                                'id' => 'FR',
                                'countryDesc' => 'name 2',
                            ],
                            [
                                'id' => 'DE',
                                'countryDesc' => 'name 3',
                            ],
                            [
                                'id' => 'PT',
                                'countryDesc' => 'name 4',
                            ],
                        ]
                    ],
                    IrhpApplicationDataSource::DATA_KEY => [
                        'irhpPermitApplications' => []
                    ],
                ],
                'expectedValueOptions' => [
                    [
                        'value' => 'NL',
                        'label' => 'name 1',
                        'hint' => 'name 1',
                    ],
                    [
                        'value' => 'FR',
                        'label' => 'name 2',
                        'hint' => 'name 2',
                    ],
                    [
                        'value' => 'DE',
                        'label' => 'name 3',
                        'hint' => 'name 3',
                    ],
                    [
                        'value' => 'PT',
                        'label' => 'name 4',
                        'hint' => 'name 4',
                    ],
                ],
                'expectedValue' => [],
            ],
            'list with data - some already selected' => [
                'data' => [
                    AvailableCountriesDataSource::DATA_KEY => [
                        'countries' => [
                            [
                                'id' => 'NL',
                                'countryDesc' => 'name 1',
                            ],
                            [
                                'id' => 'FR',
                                'countryDesc' => 'name 2',
                            ],
                            [
                                'id' => 'DE',
                                'countryDesc' => 'name 3',
                            ],
                            [
                                'id' => 'PT',
                                'countryDesc' => 'name 4',
                            ],
                        ]
                    ],
                    IrhpApplicationDataSource::DATA_KEY => [
                        'irhpPermitApplications' => [
                            [
                                'irhpPermitWindow' => [
                                    'irhpPermitStock' => [
                                        'country' => [
                                            'id' => 'NL',
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'irhpPermitWindow' => [
                                    'irhpPermitStock' => [
                                        'country' => [
                                            'id' => 'DE',
                                        ],
                                    ],
                                ],
                            ],
                        ]
                    ],
                ],
                'expected' => [
                    AvailableCountriesDataSource::DATA_KEY => [
                        'countries' => [
                            [
                                'id' => 'NL',
                                'countryDesc' => 'name 1',
                            ],
                            [
                                'id' => 'FR',
                                'countryDesc' => 'name 2',
                            ],
                            [
                                'id' => 'DE',
                                'countryDesc' => 'name 3',
                            ],
                            [
                                'id' => 'PT',
                                'countryDesc' => 'name 4',
                            ],
                        ]
                    ],
                    IrhpApplicationDataSource::DATA_KEY => [
                        'irhpPermitApplications' => [
                            [
                                'irhpPermitWindow' => [
                                    'irhpPermitStock' => [
                                        'country' => [
                                            'id' => 'NL',
                                        ],
                                    ],
                                ],
                            ],
                            [
                                'irhpPermitWindow' => [
                                    'irhpPermitStock' => [
                                        'country' => [
                                            'id' => 'DE',
                                        ],
                                    ],
                                ],
                            ],
                        ]
                    ],
                ],
                'expectedValueOptions' => [
                    [
                        'value' => 'NL',
                        'label' => 'name 1',
                        'hint' => 'name 1',
                    ],
                    [
                        'value' => 'FR',
                        'label' => 'name 2',
                        'hint' => 'name 2',
                    ],
                    [
                        'value' => 'DE',
                        'label' => 'name 3',
                        'hint' => 'name 3',
                    ],
                    [
                        'value' => 'PT',
                        'label' => 'name 4',
                        'hint' => 'name 4',
                    ],
                ],
                'expectedValue' => ['NL', 'DE'],
            ],
        ];
    }
}
