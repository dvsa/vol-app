<?php

/**
 * ApplicationFormPopulator Test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace OlcsTest\Service\Permits\Bilateral;

use Olcs\Service\Permits\Bilateral\ApplicationFormPopulator;
use Olcs\Service\Permits\Bilateral\CountryFieldsetGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Factory as FormFactory;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;

/**
 * ApplicationFormPopulator Test
 *
 * @author Jonathan Thomas <jonthan@opalise.co.uk>
 */
class ApplicationFormPopulatorTest extends TestCase
{
    public function testPopulate()
    {
        $country1Data = [
            'country1Key1' => 'country1Value1',
            'country1Key2' => 'country1Value2'
        ];

        $country2Data = [
            'country2Key1' => 'country2Value1',
            'country2Key2' => 'country2Value2'
        ];

        $data = [
            'selectedCountryIds' => ['NO', 'CH'],
            'bilateralMetadata' => [
                'countries' => [
                    $country1Data,
                    $country2Data
                ]
            ]

        ];

        $country1Fieldset = m::mock(Fieldset::class);
        $country2Fieldset = m::mock(Fieldset::class);

        $countryFieldsetGenerator = m::mock(CountryFieldsetGenerator::class);
        $countryFieldsetGenerator->shouldReceive('generate')
            ->with($country1Data)
            ->andReturn($country1Fieldset);
        $countryFieldsetGenerator->shouldReceive('generate')
            ->with($country2Data)
            ->andReturn($country2Fieldset);

        $countriesFieldset = m::mock(Fieldset::class);
        $countriesFieldset->shouldReceive('add')
            ->with($country1Fieldset)
            ->once()
            ->ordered();
        $countriesFieldset->shouldReceive('add')
            ->with($country2Fieldset)
            ->once()
            ->ordered();

        $selectedCountriesCsvHiddenParams = [
            'type' => Hidden::class,
            'name' => 'selectedCountriesCsv',
            'attributes' => [
                'id' => 'selectedCountriesCsv',
                'value' => 'NO,CH'
            ]
        ];

        $fieldsFieldset = m::mock(Fieldset::class);
        $fieldsFieldset->shouldReceive('add')
            ->with($selectedCountriesCsvHiddenParams)
            ->once();
        $fieldsFieldset->shouldReceive('add')
            ->with($countriesFieldset)
            ->once();

        $fieldsFieldsetParams = [
            'type' => Fieldset::class,
            'name' => 'fields',
            'attributes' => [
                'id' => 'bilateralContainer'
            ]
        ];

        $countriesFieldsetParams = [
            'type' => Fieldset::class,
            'name' => 'countries'
        ];

        $formFactory = m::mock(FormFactory::class);
        $formFactory->shouldReceive('create')
            ->with($fieldsFieldsetParams)
            ->andReturn($fieldsFieldset);
        $formFactory->shouldReceive('create')
            ->with($countriesFieldsetParams)
            ->andReturn($countriesFieldset);

        $form = m::mock(Form::class);
        $form->shouldReceive('add')
            ->with($fieldsFieldset)
            ->once();

        $applicationFormPopulator = new ApplicationFormPopulator($formFactory, $countryFieldsetGenerator);
        $applicationFormPopulator->populate($form, $data);
    }
}
