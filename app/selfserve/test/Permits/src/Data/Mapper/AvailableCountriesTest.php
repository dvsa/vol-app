<?php

declare(strict_types=1);

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
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestMapForFormOptions')]
    public function testMapForFormOptions(array $data, array $expected, array $expectedValueOptions, array $expectedValue): void
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

        $availableCountries = new AvailableCountries();

        self::assertEquals(
            $expected,
            $availableCountries->mapForFormOptions($data, $mockForm)
        );
    }

    /**
     * @return ((string|string[][])[]|string)[][][]
     *
     * @psalm-return array{'empty list': array{data: array{countries: array{countries: array<never, never>}, application: array{countrys: array<never, never>}}, expected: array{countries: array{countries: array<never, never>}, application: array{countrys: array<never, never>}}, expectedValueOptions: array<never, never>, expectedValue: array<never, never>}, 'list with data - nothing already selected': array{data: array{countries: array{countries: list{array{id: 'NL', countryDesc: 'name 1'}, array{id: 'FR', countryDesc: 'name 2'}, array{id: 'DE', countryDesc: 'name 3'}, array{id: 'PT', countryDesc: 'name 4'}}}, application: array{countrys: array<never, never>}}, expected: array{countries: array{countries: list{array{id: 'NL', countryDesc: 'name 1'}, array{id: 'FR', countryDesc: 'name 2'}, array{id: 'DE', countryDesc: 'name 3'}, array{id: 'PT', countryDesc: 'name 4'}}}, application: array{countrys: array<never, never>}}, expectedValueOptions: list{array{value: 'NL', label: 'name 1', hint: 'name 1'}, array{value: 'FR', label: 'name 2', hint: 'name 2'}, array{value: 'DE', label: 'name 3', hint: 'name 3'}, array{value: 'PT', label: 'name 4', hint: 'name 4'}}, expectedValue: array<never, never>}, 'list with data - some already selected': array{data: array{countries: array{countries: list{array{id: 'NL', countryDesc: 'name 1'}, array{id: 'FR', countryDesc: 'name 2'}, array{id: 'DE', countryDesc: 'name 3'}, array{id: 'PT', countryDesc: 'name 4'}}}, application: array{countrys: list{array{id: 'NL'}, array{id: 'DE'}}}}, expected: array{countries: array{countries: list{array{id: 'NL', countryDesc: 'name 1'}, array{id: 'FR', countryDesc: 'name 2'}, array{id: 'DE', countryDesc: 'name 3'}, array{id: 'PT', countryDesc: 'name 4'}}}, application: array{countrys: list{array{id: 'NL'}, array{id: 'DE'}}}}, expectedValueOptions: list{array{value: 'NL', label: 'name 1', hint: 'name 1'}, array{value: 'FR', label: 'name 2', hint: 'name 2'}, array{value: 'DE', label: 'name 3', hint: 'name 3'}, array{value: 'PT', label: 'name 4', hint: 'name 4'}}, expectedValue: list{'NL', 'DE'}}}
     */
    public static function dpTestMapForFormOptions(): array
    {
        return [
            'empty list' => [
                'data' => [
                    AvailableCountriesDataSource::DATA_KEY => [
                        'countries' => []
                    ],
                    IrhpApplicationDataSource::DATA_KEY => [
                        'countrys' => []
                    ],
                ],
                'expected' => [
                    AvailableCountriesDataSource::DATA_KEY => [
                        'countries' => []
                    ],
                    IrhpApplicationDataSource::DATA_KEY => [
                        'countrys' => []
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
                        'countrys' => []
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
                        'countrys' => []
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
                        'countrys' => [
                            [
                                'id' => 'NL',
                            ],
                            [
                                'id' => 'DE',
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
                        'countrys' => [
                            [
                                'id' => 'NL',
                            ],
                            [
                                'id' => 'DE',
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
