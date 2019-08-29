<?php

namespace PermitsTest\Data\Mapper;

use Mockery as m;
use Permits\Controller\Config\DataSource\PermitApplication as PermitAppDataSource;
use Permits\Data\Mapper\RestrictedCountries;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Zend\Form\Form;

class RestrictedCountriesTest extends TestCase
{
    private $restrictedCountries;

    public function setUp()
    {
        $this->restrictedCountries = new RestrictedCountries();
    }

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

        self::assertEquals(
            $expected,
            $this->restrictedCountries->mapFromForm($data)
        );
    }

    public function testPreprocessFormData()
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
            'formData' => $data
        ];

        $mockForm = m::mock(Form::class);

        self::assertEquals(
            $expected,
            $this->restrictedCountries->preprocessFormData($data, $mockForm)
        );
    }

    public function testPreprocessFormDataYesNoCountries()
    {
        $data = [
            'fields' => [
                'restrictedCountries' => 1,
                'yesContent' => [
                    'restrictedCountriesList' => []
                ]
            ]
        ];

        $expected = [
            'formData' => $data,
            'invalidForm' => true
        ];

        $mockForm = m::mock(Form::class);
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
            ->andReturnSelf()
            ->shouldReceive('setMessages')
            ->with(['error.messages.restricted.countries.list']);

        self::assertEquals(
            $expected,
            $this->restrictedCountries->preprocessFormData($data, $mockForm)
        );
    }

    public function testMapFormOptions()
    {
        $inputData = [
            PermitAppDataSource::DATA_KEY => [
                'id' => 9,
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

        $this->assertEquals(
            $outputData,
            $this->restrictedCountries->mapForFormOptions($inputData, $mockForm)
        );
    }

    public function testMapFormOptionsHasCountries()
    {
        $inputData = [
            PermitAppDataSource::DATA_KEY => [
                'id' => 9,
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

        $this->assertEquals(
            $outputData,
            $this->restrictedCountries->mapForFormOptions($inputData, $mockForm)
        );
    }
}
