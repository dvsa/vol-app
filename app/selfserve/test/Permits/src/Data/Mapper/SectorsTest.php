<?php

namespace PermitsTest\Data\Mapper;

use Common\Form\Form;
use Permits\Controller\Config\DataSource\Sectors as SectorsDataSource;
use Permits\Data\Mapper\Sectors;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;

/**
 * SectorsTest
 */
class SectorsTest extends TestCase
{
    public function testMapForFormOptions()
    {
        $option3Id = 3;
        $option3Name = 'Food';
        $option3Description = 'Food and drinks';

        $option4Id = 4;
        $option4Name = 'Minerals';
        $option4Description = 'Minerals and other raw materials';

        $option5Id = 5;
        $option5Name = 'Car parts';
        $option5Description = 'Automotive accessories and parts';

        $data = [
            SectorsDataSource::DATA_KEY => [
                'results' => [
                    [
                        'id' => $option3Id,
                        'name' => $option3Name,
                        'description' => $option3Description,
                    ],
                    [
                        'id' => $option4Id,
                        'name' => $option4Name,
                        'description' => $option4Description,
                    ],
                    [
                        'id' => $option5Id,
                        'name' => $option5Name,
                        'description' => $option5Description,
                    ],
                ],
            ],
            'application' => [
                'sectors' => [
                    'id' => $option4Id,
                ],
            ],
        ];

        $expectedValueOptions = [
            [
                'value' => $option3Id,
                'label' => $option3Name,
                'hint' => $option3Description,
                'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s'],
                'selected' => false,
            ],
            [
                'value' => $option4Id,
                'label' => $option4Name,
                'hint' => $option4Description,
                'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s'],
                'selected' => true,
            ],
            [
                'value' => $option5Id,
                'label' => $option5Name,
                'hint' => $option5Description,
                'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s'],
                'selected' => false,
            ],
        ];

        $form = m::mock(Form::class);
        $form->shouldReceive('get')
            ->with('fields')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('get')
            ->with('sector')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('setValueOptions')
            ->with($expectedValueOptions)
            ->once();

        $sectors = new Sectors();
        $returnedData = $sectors->mapForFormOptions($data, $form);

        $this->assertEquals($returnedData, $data);
    }
}
