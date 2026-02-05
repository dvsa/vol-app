<?php

declare(strict_types=1);

namespace PermitsTest\Data\Mapper;

use Common\Form\Elements\Types\Html;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use Permits\Controller\Config\DataSource\AvailableCountries;
use Permits\Data\Mapper\RemovedCountries;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;

/**
 * RemovedCountriesTest
 */
class RemovedCountriesTest extends TestCase
{
    public function testMapForFormOptions(): void
    {
        $validatedSelectedCountryCodesCsv = 'FR,CH';

        $data = [
            AvailableCountries::DATA_KEY => [
                'countries' => [
                    [
                        'id' => 'FR',
                        'countryDesc' => 'France'
                    ],
                    [
                        'id' => 'NO',
                        'countryDesc' => 'Norway'
                    ],
                    [
                        'id' => 'SE',
                        'countryDesc' => 'Sweden'
                    ],
                    [
                        'id' => 'CH',
                        'countryDesc' => 'Switzerland'
                    ]
                ]
            ],
            'removedCountryCodes' => ['NO', 'SE'],
            'validatedSelectedCountryCodesCsv' => $validatedSelectedCountryCodesCsv,
            'otherKey1' => 'otherValue1',
            'otherKey2' => 'otherValue2'
        ];

        $expectedMarkup = '<ul class="govuk-list govuk-list--bullet"><li>Norway</li><li>Sweden</li></ul>';

        $removedCountriesElement = m::mock(Html::class);
        $removedCountriesElement->shouldReceive('setValue')
            ->with($expectedMarkup)
            ->once();

        $countriesElement = m::mock(Hidden::class);
        $countriesElement->shouldReceive('setValue')
            ->with($validatedSelectedCountryCodesCsv)
            ->once();

        $fieldset = m::mock(Fieldset::class);
        $fieldset->shouldReceive('get')
            ->with('removedCountries')
            ->andReturn($removedCountriesElement);
        $fieldset->shouldReceive('get')
            ->with('countries')
            ->andReturn($countriesElement);

        $form = m::mock(Form::class);
        $form->shouldReceive('get')
            ->with('fields')
            ->andReturn($fieldset);

        $removedCountries = new RemovedCountries();

        $this->assertEquals(
            $data,
            $removedCountries->mapForFormOptions($data, $form)
        );
    }
}
