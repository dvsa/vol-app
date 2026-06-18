<?php

namespace CommonTest\Service\Qa\Custom\Ecmt;

use Common\Service\Helper\TranslationHelperService;
use Common\Service\Qa\Custom\Common\HtmlAdder;
use Common\Service\Qa\Custom\Ecmt\AnnualTripsAbroadFieldsetPopulator;
use Common\Service\Qa\Custom\Ecmt\NiWarningConditionalAdder;
use Common\Service\Qa\TextFieldsetPopulator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;

/**
 * AnnualTripsAbroadFieldsetPopulatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class AnnualTripsAbroadFieldsetPopulatorTest extends MockeryTestCase
{
    /**
     * @dataProvider dpTrueFalse
     */
    public function testPopulate($showNiWarning): void
    {
        $markup = '<div class="govuk-inset-text">Trips abroad guidance</div><p>paragraph 1</p><p>paragraph</p>';

        $textOptions = [
            'textKey1' => 'textValue1',
            'textKey2' => 'textValue2'
        ];

        $options = [
            'showNiWarning' => $showNiWarning,
            'text' => $textOptions
        ];

        $expectedWarningVisibleParameters = [
            'name' => 'warningVisible',
            'type' => Hidden::class,
            'attributes' => [
                'value' => 0
            ]
        ];

        $form = m::mock(Form::class);

        $fieldset = m::mock(Fieldset::class);
        $fieldset->shouldReceive('add')
            ->with($expectedWarningVisibleParameters)
            ->once();

        $niWarningConditionalAdder = m::mock(NiWarningConditionalAdder::class);
        $niWarningConditionalAdder->shouldReceive('addIfRequired')
            ->with($fieldset, $showNiWarning)
            ->once()
            ->globally()
            ->ordered();

        $htmlAdder = m::mock(HtmlAdder::class);
        $htmlAdder->shouldReceive('add')
            ->with($fieldset, 'hint', $markup)
            ->once()
            ->globally()
            ->ordered();

        $textFieldsetPopulator = m::mock(TextFieldsetPopulator::class);
        $textFieldsetPopulator->shouldReceive('populate')
            ->with($form, $fieldset, $textOptions)
            ->once()
            ->globally()
            ->ordered();

        $translator = m::mock(TranslationHelperService::class);
        $translator->shouldReceive('translate')
            ->with('markup-ecmt-trips-hint')
            ->andReturn('<p>paragraph 1</p><p>paragraph</p>');
        $translator->shouldReceive('translate')
            ->with('qanda.ecmt.annual-trips-abroad.guidance')
            ->andReturn('Trips abroad guidance');

        $annualTripsAbroadFieldsetPopulator = new AnnualTripsAbroadFieldsetPopulator(
            $textFieldsetPopulator,
            $translator,
            $niWarningConditionalAdder,
            $htmlAdder
        );

        $annualTripsAbroadFieldsetPopulator->populate($form, $fieldset, $options);
    }

    /**
     * @return bool[][]
     *
     * @psalm-return list{list{true}, list{false}}
     */
    public function dpTrueFalse(): array
    {
        return [
            [true],
            [false]
        ];
    }
}
