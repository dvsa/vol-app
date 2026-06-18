<?php

namespace CommonTest\Service\Qa\Custom\Ecmt;

use Common\Service\Qa\Custom\Ecmt\RestrictedCountriesFieldsetPopulator;
use Common\Service\Qa\Custom\Ecmt\YesNoRadio;
use Common\Service\Qa\Custom\Ecmt\YesNoRadioFactory;
use Common\Service\Qa\Custom\Ecmt\RestrictedCountriesMultiCheckbox;
use Common\Service\Qa\Custom\Ecmt\RestrictedCountriesMultiCheckboxFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;

/**
 * RestrictedCountriesFieldsetPopulatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class RestrictedCountriesFieldsetPopulatorTest extends MockeryTestCase
{
    /**
     * @dataProvider dpTestPopulate
     */
    public function testPopulate($options, $expectedValueOptions, $expectedSetValue): void
    {
        $fieldsetName = 'fieldset12';
        $yesNoRadioName = 'restrictedCountries';
        $multiCheckboxName = 'yesContent';

        $form = m::mock(Form::class);

        $yesNoRadio = m::mock(YesNoRadio::class);
        $yesNoRadio->shouldReceive('setStandardValueOptions')
            ->withNoArgs()
            ->once();
        $yesNoRadio->shouldReceive('setValue')
            ->with($expectedSetValue)
            ->once();

        $restrictedCountriesMultiCheckbox = m::mock(RestrictedCountriesMultiCheckbox::class);
        $restrictedCountriesMultiCheckbox->shouldReceive('setValueOptions')
            ->with($expectedValueOptions)
            ->once();

        $fieldset = m::mock(Fieldset::class);
        $fieldset->shouldReceive('getName')
            ->andReturn($fieldsetName);
        $fieldset->shouldReceive('setOption')
            ->with('radio-element', 'restrictedCountries')
            ->once();
        $fieldset->shouldReceive('add')
            ->with($yesNoRadio)
            ->once()
            ->ordered();
        $fieldset->shouldReceive('add')
            ->with($restrictedCountriesMultiCheckbox)
            ->once()
            ->ordered();

        $fieldset->shouldReceive('setLabel')
            ->with('question.key')
            ->once();
        $fieldset->shouldReceive('setLabelAttributes')
            ->with(['class' => 'govuk-visually-hidden'])
            ->once();

        $yesNoRadioFactory = m::mock(YesNoRadioFactory::class);
        $yesNoRadioFactory->shouldReceive('create')
            ->with($yesNoRadioName)
            ->once()
            ->andReturn($yesNoRadio);

        $restrictedCountriesMultiCheckboxFactory = m::mock(RestrictedCountriesMultiCheckboxFactory::class);
        $restrictedCountriesMultiCheckboxFactory->shouldReceive('create')
            ->with($multiCheckboxName)
            ->once()
            ->andReturn($restrictedCountriesMultiCheckbox);

        $yesNoRadio->shouldReceive('setOption')
            ->with('yesContentElement', $restrictedCountriesMultiCheckbox)
            ->once();

        $restrictedCountriesFieldsetPopulator = new RestrictedCountriesFieldsetPopulator(
            $yesNoRadioFactory,
            $restrictedCountriesMultiCheckboxFactory
        );

        $restrictedCountriesFieldsetPopulator->populate($form, $fieldset, $options);
    }

    /**
     * @return ((((bool|string)[]|bool|string)[]|bool|null|string)[]|null|string)[][]
     *
     * @psalm-return list{array{options: array{yesNo: null, questionKey: 'question.key', countries: array<never, never>}, expectedValueOptions: array<never, never>, expectedSetValue: null}, array{options: array{yesNo: false, questionKey: 'question.key', countries: array<never, never>}, expectedValueOptions: array<never, never>, expectedSetValue: 'N'}, array{options: array{yesNo: true, questionKey: 'question.key', countries: list{array{code: 'GR', labelTranslationKey: 'Greece', checked: true}, array{code: 'HU', labelTranslationKey: 'Hungary', checked: false}, array{code: 'IT', labelTranslationKey: 'Italy', checked: true}}}, expectedValueOptions: list{array{value: 'GR', label: 'Greece', selected: true, attributes: array{id: 'RestrictedCountriesList'}}, array{value: 'HU', label: 'Hungary', selected: false}, array{value: 'IT', label: 'Italy', selected: true}}, expectedSetValue: 'Y'}}
     */
    public function dpTestPopulate(): array
    {
        return [
            [
                'options' => [
                    'yesNo' => null,
                    'questionKey' => 'question.key',
                    'countries' => [],
                ],
                'expectedValueOptions' => [],
                'expectedSetValue' => null,
            ],
            [
                'options' => [
                    'yesNo' => false,
                    'questionKey' => 'question.key',
                    'countries' => [],
                ],
                'expectedValueOptions' => [],
                'expectedSetValue' => 'N',
            ],
            [
                'options' => [
                    'yesNo' => true,
                    'questionKey' => 'question.key',
                    'countries' => [
                        [
                            'code' => 'GR',
                            'labelTranslationKey' => 'Greece',
                            'checked' => true
                        ],
                        [
                            'code' => 'HU',
                            'labelTranslationKey' => 'Hungary',
                            'checked' => false
                        ],
                        [
                            'code' => 'IT',
                            'labelTranslationKey' => 'Italy',
                            'checked' => true
                        ],
                    ],
                ],
                'expectedValueOptions' => [
                    [
                        'value' => 'GR',
                        'label' => 'Greece',
                        'selected' => true,
                        'attributes' => [
                            'id' => 'RestrictedCountriesList',
                        ],
                    ],
                    [
                        'value' => 'HU',
                        'label' => 'Hungary',
                        'selected' => false
                    ],
                    [
                        'value' => 'IT',
                        'label' => 'Italy',
                        'selected' => true
                    ],
                ],
                'expectedSetValue' => 'Y',
            ],
        ];
    }
}
