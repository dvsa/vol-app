<?php

namespace CommonTest\Service\Qa\Custom\Bilateral;

use Common\Service\Helper\TranslationHelperService;
use Common\Service\Qa\Custom\Bilateral\CabotageOnlyFieldsetPopulator;
use Common\Service\Qa\Custom\Bilateral\StandardYesNoValueOptionsGenerator;
use Common\Service\Qa\Custom\Bilateral\YesNoWithMarkupForNoPopulator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;

/**
 * CabotageOnlyFieldsetPopulatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class CabotageOnlyFieldsetPopulatorTest extends MockeryTestCase
{
    public function testPopulate(): void
    {
        $cabotageOnlyNoBlurb = 'Cabotage only no blurb %s';

        $yesNo = 'yesNo';

        $countryName = 'Norway';
        $countryNameTranslated = 'NorwayTranslated';

        $options = [
            'yesNo' => $yesNo,
            'countryName' => $countryName
        ];

        $fieldset = m::mock(Fieldset::class);
        $fieldset->shouldReceive('setLabel')
            ->with('qanda.bilaterals.cabotage.question')
            ->once();
        $fieldset->shouldReceive('setLabelAttributes')
            ->with(['class' => 'govuk-visually-hidden'])
            ->once();

        $form = m::mock(Form::class);

        $translator = m::mock(TranslationHelperService::class);
        $translator->shouldReceive('translate')
            ->with('qanda.bilaterals.cabotage-only.no-blurb')
            ->andReturn($cabotageOnlyNoBlurb);
        $translator->shouldReceive('translate')
            ->with($countryName)
            ->andReturn($countryNameTranslated);

        $standardYesNoValueOptions = [
            'key1' => 'value1',
            'key2' => 'value2'
        ];

        $standardYesNoValueOptionsGenerator = m::mock(StandardYesNoValueOptionsGenerator::class);
        $standardYesNoValueOptionsGenerator->shouldReceive('generate')
            ->withNoArgs()
            ->andReturn($standardYesNoValueOptions);

        $yesNoWithMarkupForNoPopulator = m::mock(YesNoWithMarkupForNoPopulator::class);
        $yesNoWithMarkupForNoPopulator->shouldReceive('populate')
            ->with(
                $fieldset,
                $standardYesNoValueOptions,
                'Cabotage only no blurb NorwayTranslated',
                $yesNo,
                'qanda.bilaterals.cabotage.not-selected-message'
            )
            ->once();

        $cabotageOnlyFieldsetPopulator = new CabotageOnlyFieldsetPopulator(
            $translator,
            $yesNoWithMarkupForNoPopulator,
            $standardYesNoValueOptionsGenerator
        );

        $cabotageOnlyFieldsetPopulator->populate($form, $fieldset, $options);
    }
}
