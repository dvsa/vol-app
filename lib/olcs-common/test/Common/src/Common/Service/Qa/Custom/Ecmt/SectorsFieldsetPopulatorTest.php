<?php

namespace CommonTest\Service\Qa\Custom\Ecmt;

use Common\Service\Helper\TranslationHelperService;
use Common\Service\Qa\Custom\Ecmt\SectorsFieldsetPopulator;
use Common\Service\Qa\RadioFieldsetPopulator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Element\Radio;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;

/**
 * SectorsFieldsetPopulatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class SectorsFieldsetPopulatorTest extends MockeryTestCase
{
    public function testPopulate(): void
    {
        $options = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];

        $valueOptionsBeforeModification = [
            [
                'value' => 'option1value',
                'label' => 'Option 1'
            ],
            [
                'value' => 'option2value',
                'label' => 'Option 2'
            ],
            [
                'value' => 'option3value',
                'label' => 'Option 3'
            ],
        ];

        $expectedValueOptionsAfterModification = [
            [
                'value' => 'option1value',
                'label' => 'Option 1'
            ],
            [
                'value' => 'option2value',
                'label' => 'Option 2'
            ],
            [
                'value' => 'option3value',
                'label' => 'Option 3',
                'markup_before' => '<div class="govuk-radios__divider">translated-or</div>',
            ],
        ];

        $form = m::mock(Form::class);
        $fieldset = m::mock(Fieldset::class);

        $translator = m::mock(TranslationHelperService::class);
        $translator->shouldReceive('translate')
            ->with('qanda.ecmt.sectors.divider.or')
            ->once()
            ->andReturn('translated-or');

        $radioFieldsetPopulator = m::mock(RadioFieldsetPopulator::class);
        $radioFieldsetPopulator->shouldReceive('populate')
            ->with($form, $fieldset, $options)
            ->once()
            ->globally()
            ->ordered();

        $radio = m::mock(Radio::class);
        $radio->shouldReceive('getValueOptions')
            ->withNoArgs()
            ->once()
            ->globally()
            ->ordered()
            ->andReturn($valueOptionsBeforeModification);
        $radio->shouldReceive('setValueOptions')
            ->with($expectedValueOptionsAfterModification)
            ->once()
            ->globally()
            ->ordered();

        $fieldset->shouldReceive('get')
            ->with('qaElement')
            ->andReturn($radio);

        $sectorsFieldsetPopulator = new SectorsFieldsetPopulator($translator, $radioFieldsetPopulator);
        $sectorsFieldsetPopulator->populate($form, $fieldset, $options);
    }
}
