<?php

declare(strict_types=1);

namespace PermitsTest\Data\Mapper;

use Common\Form\Element\DynamicRadio;
use Common\Form\Elements\Types\Html;
use Common\Form\Form;
use Common\Form\Input\StockInputMorocco;
use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Mockery as m;
use Permits\Controller\Config\DataSource\IrhpApplication as IrhpApplicationDataSource;
use Permits\Data\Mapper\AvailableBilateralStocks;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Submit;
use Laminas\Form\Fieldset;

class AvailableBilateralStocksTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    public $translationHelperService;

    public $sut;

    public function setUp(): void
    {
        $this->translationHelperService = m::mock(TranslationHelperService::class);
        $this->sut = new AvailableBilateralStocks($this->translationHelperService);
    }

    public function testMapForFormOptionsSingle(): void
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

        $this->translationHelperService
            ->shouldReceive('translate')
            ->once()
            ->with('i.am.a.key')
            ->andReturn('im no longer a key, im translated!')
            ->shouldReceive('translate')
            ->once()
            ->with('permits.page.bilateral.which-period-required.single-stock.text')
            ->andReturn('single stock text');

        $mockFieldSet = m::mock(Fieldset::class);
        $mockForm = m::mock(Form::class);

        $mockForm->shouldReceive('get')->once()->with('fields')->andReturn($mockFieldSet);

        $mockFieldSet->shouldReceive('add')->once()->with([
            'name' => 'irhpPermitStockLabel',
            'type' => Html::class,
            'attributes' => [
                'value' => '<p class="govuk-body-l">single stock text im no longer a key, im translated!</p>',
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

    public function testMapForFormOptionsMultipleNotMorocco(): void
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
        $mockField = m::mock(DynamicRadio::class);

        $mockSubmitButtonField = m::mock(Submit::class);
        $mockSubmitButtonField->shouldReceive('setValue')
            ->with('Save and continue')
            ->once();

        $mockSubmitFieldset = m::mock(Fieldset::class);
        $mockSubmitFieldset->shouldReceive('get')
            ->with('SubmitButton')
            ->andReturn($mockSubmitButtonField);

        $mockForm->shouldReceive('get')
            ->with('Submit')
            ->andReturn($mockSubmitFieldset);

        $valueOptions = [
            [
                'value' => 12,
                'label' => 'i.am.a.key',
                'attributes' => [
                    'id' => 'stock'
                ]
            ],
            [
                'value' => 13,
                'label' => 'i.am.another.key',
            ]
        ];

        $mockForm->expects('get')->with('fields')->andReturn($mockFieldSet);
        $mockFieldSet->shouldReceive('get')->once()->with('irhpPermitStock')->andReturn($mockField);
        $mockField->shouldReceive('setValueOptions')->with($valueOptions)->once();
        $mockField->shouldReceive('setValue')->with(12)->once();

        $outputData = $inputData;
        $outputData[IrhpApplicationDataSource::DATA_KEY]['countryName'] = 'Norway';
        $outputData[IrhpApplicationDataSource::DATA_KEY]['selectedStockId'] = 12;
        $outputData['question'] = 'permits.page.bilateral.which-period-required.multi-stock';
        $outputData['browserTitle'] = 'permits.page.bilateral.which-period-required.multi-stock';

        $this->assertEquals(
            $outputData,
            $this->sut->mapForFormOptions($inputData, $mockForm)
        );
    }

    public function testMapForFormOptionsMultipleMorocco(): void
    {
        $inputData = [
            IrhpApplicationDataSource::DATA_KEY => [
                'countrys' => [
                    [
                        'id' => 'MA',
                        'countryDesc' => 'Morocco'
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
                                    'id' => 'MA'
                                ]
                            ]
                        ]
                    ]
                ],
            ],
            'stocks' => [
                [
                    'id' => 12,
                    'periodNameKey' => 'period.12.label'
                ],
                [
                    'id' => 13,
                    'periodNameKey' => 'period.13.label'
                ]
            ],
            'routeParams' => [
                'country' => 'MA'
            ]
        ];

        $mockFieldSet = m::mock(Fieldset::class);
        $mockForm = m::mock(Form::class);
        $mockField = m::mock(DynamicRadio::class);

        $mockSubmitButtonField = m::mock(Submit::class);
        $mockSubmitButtonField->shouldReceive('setValue')
            ->with('Save and continue')
            ->once();

        $mockSubmitFieldset = m::mock(Fieldset::class);
        $mockSubmitFieldset->shouldReceive('get')
            ->with('SubmitButton')
            ->andReturn($mockSubmitButtonField);

        $mockForm->shouldReceive('get')
            ->with('Submit')
            ->andReturn($mockSubmitFieldset);

        $valueOptions = [
            [
                'value' => 12,
                'label' => 'period.12.label',
                'hint' => 'period.12.hint',
                'attributes' => [
                    'id' => 'stock'
                ]
            ],
            [
                'value' => 13,
                'label' => 'period.13.label',
                'hint' => 'period.13.hint'
            ]
        ];

        $mockForm->shouldReceive('get')->with('fields')->andReturn($mockFieldSet);
        $mockFieldSet->shouldReceive('get')->with('irhpPermitStock')->andReturn($mockField);
        $mockField->shouldReceive('setValueOptions')->with($valueOptions)->once();
        $mockField->shouldReceive('setValue')->with(12)->once();
        $mockField->shouldReceive('setOption')
            ->with('input_class', StockInputMorocco::class)
            ->once();

        $outputData = $inputData;
        $outputData[IrhpApplicationDataSource::DATA_KEY]['countryName'] = 'Morocco';
        $outputData[IrhpApplicationDataSource::DATA_KEY]['selectedStockId'] = 12;
        $outputData['question'] = 'permits.page.bilateral.which-period-required.morocco';
        $outputData['browserTitle'] = 'permits.page.bilateral.which-period-required.morocco';

        $this->assertEquals(
            $outputData,
            $this->sut->mapForFormOptions($inputData, $mockForm)
        );
    }

    public function testProcessRedirectParams(): void
    {
        $stockId7Slug = 'slug-seven';

        $response = [
            'id' => [
                'irhpPermitApplication' => 4545,
            ]
        ];

        $routeParams = [
            'id' => 12
        ];

        $formData = [
            'fields' => [
                'irhpPermitStock' => '7'
            ]
        ];

        $data = [
            'stocks' => [
                [
                    'id' => 1,
                    'first_step_slug' => 'slug-one'
                ],
                [
                    'id' => 7,
                    'first_step_slug' => $stockId7Slug
                ],
                [
                    'id' => 13,
                    'first_step_slug' => 'slug-thirteen'
                ],
            ]
        ];

        $this->assertEquals(
            [
                'id' => $routeParams['id'],
                'irhpPermitApplication' => $response['id']['irhpPermitApplication'],
                'slug' => $stockId7Slug
            ],
            $this->sut->processRedirectParams($response, $routeParams, $formData, $data)
        );
    }
}
