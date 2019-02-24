<?php

namespace PermitsTest\Data\Mapper;

use Mockery as m;
use Common\RefData;
use Permits\Controller\Config\DataSource\PermitApplication as PermitAppDataSource;
use Permits\Data\Mapper\RestrictedCountries;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Zend\Form\Form;

class RestrictedCountriesTest extends TestCase
{
    public function testMapFromForm()
    {
        $data = [
            'fields' => [
                'restrictedCountries' => 1,
                'yesContent' => [
                    'restrictedCountriesList' => [
                        'AT', 'HU'
                    ]
                ]
            ]
        ];

        $expected = [
            'restrictedCountries' => 1,
            'countryIds' => [
                'AT', 'HU'
            ],
            'yesContent' => [
                'restrictedCountriesList' => [
                    'AT', 'HU'
                ]
            ]
        ];

        self::assertEquals($expected, RestrictedCountries::mapFromForm($data));
    }

    public function testPreprocessFormDataEuro6()
    {
        $data = [
            'fields' => [
                'restrictedCountries' => 1,
                'yesContent' => [
                    'restrictedCountriesList' => [
                        'AT', 'HU'
                    ]
                ]
            ]
        ];

        self::assertEquals($data, RestrictedCountries::preprocessFormData($data));
    }

    public function testPreprocessFormDataEuro5()
    {
        $data = [
            'euro5Fields' => [
                'restrictedCountries' => 1,
            ],
        ];

        $expected = [
            'euro5Fields' => [
                'restrictedCountries' => 1,
            ],
            'fields' => [
                'restrictedCountries' => 1,
                'yesContent' => [
                    'restrictedCountriesList' => []
                ]
            ]
        ];

        self::assertEquals($expected, RestrictedCountries::preprocessFormData($data));
    }

    public function testMapFormOptionsEuro5()
    {
        $inputData = [
            PermitAppDataSource::DATA_KEY => [
                'id' => 9,
                'windowEmissionsCategory' => RefData::EMISSIONS_CATEGORY_EURO5,
                'hasRestrictedCountries' => null
            ]
        ];

        $outputData[PermitAppDataSource::DATA_KEY] = $inputData[PermitAppDataSource::DATA_KEY];
        $outputData['guidance'] = 'permits.page.restricted-countries.guidance.euro5';
        $outputData['question'] = 'permits.page.restricted-countries.title.euro5';

        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('remove')->with('fields')->once();

        self::assertEquals($outputData, RestrictedCountries::mapForFormOptions($inputData, $mockForm));
    }

    public function testMapFormOptionsEuro5HasCountries()
    {
        $inputData = [
            PermitAppDataSource::DATA_KEY => [
                'id' => 9,
                'windowEmissionsCategory' => RefData::EMISSIONS_CATEGORY_EURO5,
                'hasRestrictedCountries' => 0
            ]
        ];

        $outputData[PermitAppDataSource::DATA_KEY] = $inputData[PermitAppDataSource::DATA_KEY];
        $outputData['guidance'] = 'permits.page.restricted-countries.guidance.euro5';
        $outputData['question'] = 'permits.page.restricted-countries.title.euro5';

        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('remove')->with('fields')->once();

        $mockForm->shouldReceive('get')
            ->with('euro5Fields')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('get')
            ->with('restrictedCountries')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setValue')
            ->with(1);

        self::assertEquals($outputData, RestrictedCountries::mapForFormOptions($inputData, $mockForm));
    }

    public function testMapFormOptionsEuro6()
    {
        $inputData = [
            PermitAppDataSource::DATA_KEY => [
                'id' => 9,
                'windowEmissionsCategory' => RefData::EMISSIONS_CATEGORY_EURO6,
                'countrys' => [
                    ['id' => 'AT']
                ],
                'hasRestrictedCountries' => null
            ],
            'ecmtConstrainedCountries' => [
                'results' => [
                    [
                        'id' => 'GR',
                        'countryDesc' => 'Greece'
                    ],
                    [
                        'id' => 'AT',
                        'countryDesc' => 'Austria'
                    ]
                ]
            ]
        ];

        $outputData[PermitAppDataSource::DATA_KEY] = $inputData[PermitAppDataSource::DATA_KEY];
        $outputData['guidance'] = [
            'permits.page.restricted-countries.guidance.line.1',
            'permits.page.restricted-countries.guidance.line.2'
        ];
        $outputData['question'] = 'permits.page.restricted-countries.question';
        $outputData['ecmtConstrainedCountries'] = [
            'results' => [
                [
                    'id' => 'GR',
                    'countryDesc' => 'Greece'
                ],
                [
                    'id' => 'AT',
                    'countryDesc' => 'Austria'
                ]
            ]
        ];

        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('remove')->with('euro5Fields')->once();

        $valueOpts = [
            [
                'value' => 'GR',
                'label' => 'Greece',
                'selected' => false
            ],
            [
                'value' => 'AT',
                'label' => 'Austria',
                'selected' => true
            ]
        ];

        $mockForm->shouldReceive('get')
            ->with('fields')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('get')
            ->with('yesContent')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('get')
            ->with('restrictedCountriesList')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setValueOptions')
            ->once()
            ->with($valueOpts);

        self::assertEquals($outputData, RestrictedCountries::mapForFormOptions($inputData, $mockForm));
    }


    public function testMapFormOptionsEuro6HasCountries()
    {
        $inputData = [
            PermitAppDataSource::DATA_KEY => [
                'id' => 9,
                'windowEmissionsCategory' => RefData::EMISSIONS_CATEGORY_EURO6,
                'countrys' => [
                    ['id' => 'AT']
                ],
                'hasRestrictedCountries' => 1
            ],
            'ecmtConstrainedCountries' => [
                'results' => [
                    [
                        'id' => 'GR',
                        'countryDesc' => 'Greece'
                    ],
                    [
                        'id' => 'AT',
                        'countryDesc' => 'Austria'
                    ]
                ]
            ]
        ];

        $outputData[PermitAppDataSource::DATA_KEY] = $inputData[PermitAppDataSource::DATA_KEY];
        $outputData['guidance'] = [
            'permits.page.restricted-countries.guidance.line.1',
            'permits.page.restricted-countries.guidance.line.2'
        ];
        $outputData['question'] = 'permits.page.restricted-countries.question';
        $outputData['ecmtConstrainedCountries'] = [
            'results' => [
                [
                    'id' => 'GR',
                    'countryDesc' => 'Greece'
                ],
                [
                    'id' => 'AT',
                    'countryDesc' => 'Austria'
                ]
            ]
        ];

        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('remove')->with('euro5Fields')->once();

        $valueOpts = [
            [
                'value' => 'GR',
                'label' => 'Greece',
                'selected' => false
            ],
            [
                'value' => 'AT',
                'label' => 'Austria',
                'selected' => true
            ]
        ];

        $mockForm->shouldReceive('get')
            ->with('fields')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('get')
            ->with('yesContent')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('get')
            ->with('restrictedCountriesList')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setValueOptions')
            ->once()
            ->with($valueOpts);

        $mockForm->shouldReceive('get')
            ->with('fields')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('get')
            ->with('restrictedCountries')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setValue')
            ->with(1);

        self::assertEquals($outputData, RestrictedCountries::mapForFormOptions($inputData, $mockForm));
    }
}
