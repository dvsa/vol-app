<?php

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\BilateralApplicationValidationModifier;
use Olcs\Service\Permits\Bilateral\ApplicationFormPopulator;
use Laminas\Form\Form;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;

/**
 * BilateralApplicationValidationModifier Mapper Test
 */
class BilateralApplicationValidationModifierTest extends MockeryTestCase
{
    private $form;

    private $applicationFormPopulator;

    private $bilateralApplicationValidationModifier;

    public function setUp(): void
    {
        $this->form = m::mock(Form::class);

        $this->applicationFormPopulator = m::mock(ApplicationFormPopulator::class);

        $this->bilateralApplicationValidationModifier = new BilateralApplicationValidationModifier(
            $this->applicationFormPopulator
        );
    }

    public function testMapForFormOptionsFieldsNotPresent()
    {
        $data = [
            'key1' => 'value1',
            'key2' => 'value2'
        ];

        $this->applicationFormPopulator->shouldReceive('populate')
            ->with($this->form, $data)
            ->once();

        $this->assertEquals(
            $data,
            $this->bilateralApplicationValidationModifier->mapForFormOptions($data, $this->form)
        );
    }

    public function testMapForFormOptionsFieldsPresent()
    {
        $data = [
            'fields' => [
                'countries' => [
                    'CH' => [
                        'selectedPeriodId' => 43,
                        'periods' => [
                            'period42' => [
                                'standard-journey_single' => '4',
                                'cabotage-journey_single' => '4'
                            ],
                            'period43' => [
                                'standard-journey_single' => '4'
                            ],
                            'period44' => [
                                'cabotage-journey_multiple' => '4'
                            ]
                        ]
                    ],
                    'NO' => [
                        'selectedPeriodId' => 51,
                        'periods' => [
                            'period51' => [
                                'standard-journey_single' => '4'
                            ],
                            'period52' => [
                                'cabotage-journey_multiple' => '4'
                            ]
                        ]
                    ],
                ]
            ],
            'selectedCountryIds' => ['CH']
        ];

        $chPeriodsInputFilter = m::mock(InputFilter::class);
        $chPeriodsInputFilter->shouldReceive('remove')
            ->with('period42')
            ->once();
        $chPeriodsInputFilter->shouldReceive('remove')
            ->with('period44')
            ->once();

        $chInput = m::mock(Input::class);
        $chInput->shouldReceive('get')
            ->with('periods')
            ->andReturn($chPeriodsInputFilter);

        $countryInputs = [
            'CH' => $chInput
        ];

        $countriesInputFilter = m::mock(InputFilter::class);
        $countriesInputFilter->shouldReceive('getInputs')
            ->withNoArgs()
            ->andReturn($countryInputs);
        $countriesInputFilter->shouldReceive('remove')
            ->with('NO')
            ->once();

        $inputFilter = m::mock(InputFilter::class);
        $inputFilter
            ->shouldReceive('get')
            ->with('fields')
            ->andReturnSelf()
            ->shouldReceive('get')
            ->with('countries')
            ->andReturn($countriesInputFilter);

        $this->form->shouldReceive('getInputFilter')
            ->withNoArgs()
            ->andReturn($inputFilter);

        $this->applicationFormPopulator->shouldReceive('populate')
            ->with($this->form, $data)
            ->once();

        $this->assertEquals(
            $data,
            $this->bilateralApplicationValidationModifier->mapForFormOptions($data, $this->form)
        );
    }
}
