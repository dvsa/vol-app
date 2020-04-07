<?php

namespace PermitsTest\Data\Mapper;

use Common\Form\Element\DynamicRadio;
use Common\Form\Elements\Types\Html;
use Common\Form\Form;
use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Mockery as m;
use Permits\Controller\Config\DataSource\IrhpApplication as IrhpApplicationDataSource;
use Permits\Data\Mapper\AvailableBilateralStocks;
use Zend\Form\Element\Hidden;
use Zend\Form\Fieldset;

class AvailableBilateralStocksTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    public $translationHelperService;

    public $sut;

    public function setUp()
    {
        $this->translationHelperService = m::mock(TranslationHelperService::class);
        $this->sut = new AvailableBilateralStocks($this->translationHelperService);
    }

    public function testMapForFormOptionsSingle()
    {
        $inputData = [
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
            'stocks' => [
                [
                    'id' => 12,
                    'periodNameKey' => 'i.am.a.key'
                ]
            ],
            'routeParams' => [
                'country' => 'NO'
            ]
        ];

        $this->translationHelperService->shouldReceive('translate')
            ->once()
            ->with('i.am.a.key')
            ->andReturn('im no longer a key, im translated!');

        $mockFieldSet = m::mock(Fieldset::class);
        $mockForm = m::mock(Form::class);

        $mockForm->shouldReceive('get')->twice()->with('fields')->andReturn($mockFieldSet);

        $mockFieldSet->shouldReceive('add')->once()->with([
            'name' => 'irhpPermitStockLabel',
            'type' => Html::class,
            'attributes' => [
                'value' => 'im no longer a key, im translated!',
            ]
        ]);

        $mockFieldSet->shouldReceive('add')->once()->with([
            'name' => 'irhpPermitStock',
            'type' => Hidden::class,
            'attributes' => [
                'value' => 12,
            ]
        ]);

        $this->assertEquals(
            $inputData,
            $this->sut->mapForFormOptions($inputData, $mockForm)
        );
    }

    public function testMapForFormOptionsMultiple()
    {
        $inputData = [
            IrhpApplicationDataSource::DATA_KEY => [
                'countrys' => [
                    [
                        'id' => 'NO',
                        'countryDesc' => 'Norway'
                    ],
                    [
                        'id' => 'DE',
                        'countryDesc' => 'Germany'
                    ],
                ],
                'irhpPermitApplications' => [
                    [
                        'irhpPermitWindow' => [
                            'irhpPermitStock' => [
                                'id' => 12,
                                'country' => [
                                    'id' => 'NO'
                                ]
                            ]
                        ]
                    ]
                ],
            ],
            'stocks' => [
                [
                    'id' => 12,
                    'periodNameKey' => 'i.am.a.key'
                ],
                [
                    'id' => 13,
                    'periodNameKey' => 'i.am.another.key'
                ]
            ],
            'routeParams' => [
                'country' => 'NO'
            ]
        ];

        $mockFieldSet = m::mock(Fieldset::class);
        $mockForm = m::mock(Form::class);

        $valueOptions = [
            [
                'value' => 12,
                'label' => 'i.am.a.key',
                'label_attributes' => [
                    'class' => 'govuk-label govuk-radios__label govuk-label--s',
                ],
                'selected' => true,
                'attributes' => [
                    'id' => 'stock'
                ]
            ],
            [
                'value' => 13,
                'label' => 'i.am.another.key',
                'label_attributes' => [
                    'class' => 'govuk-label govuk-radios__label govuk-label--s',
                ],
                'selected' => null
            ]
        ];

        $mockForm->expects('get')->with('fields')->andReturn($mockFieldSet);

        $mockFieldSet->shouldReceive('add')->once()->with([
            'name' => 'irhpPermitStock',
            'type' => DynamicRadio::class,
            'options' => [
                'value_options' => $valueOptions
            ],
        ]);

        $outputData = $inputData;
        $outputData[IrhpApplicationDataSource::DATA_KEY]['countryName'] = 'Norway';
        $this->assertEquals(
            $outputData,
            $this->sut->mapForFormOptions($inputData, $mockForm)
        );
    }

    public function testProcessRedirectParams()
    {
        $response = [
            'id' => [
                'irhpPermitApplication' => 4545,
            ]
        ];
        $routeParams = [
            'id' => 12
        ];
        $formData = [];

        $this->assertEquals(
            [
                'id' => $routeParams['id'],
                'irhpPermitApplicationId' => $response['id']['irhpPermitApplication'],
                'slug' => RefData::BILATERAL_PERMIT_USAGE
            ],
            $this->sut->processRedirectParams($response, $routeParams, $formData)
        );
    }
}
