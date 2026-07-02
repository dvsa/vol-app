<?php

namespace CommonTest\Service\Qa\Custom\Bilateral;

use Common\Service\Helper\TranslationHelperService;
use Common\Service\Qa\Custom\Bilateral\ThirdCountryFieldsetPopulator;
use Common\Service\Qa\Custom\Bilateral\StandardYesNoValueOptionsGenerator;
use Common\Service\Qa\Custom\Bilateral\YesNoWithMarkupForNoPopulator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;

/**
 * ThirdCountryFieldsetPopulatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ThirdCountryFieldsetPopulatorTest extends MockeryTestCase
{
    public function testPopulate(): void
    {
        $thirdCountryNoBlurb = 'Third country no blurb';

        $yesNo = 'yesNo';

        $options = [
            'yesNo' => $yesNo
        ];

        $fieldset = m::mock(Fieldset::class);

        $form = m::mock(Form::class);

        $translator = m::mock(TranslationHelperService::class);
        $translator->shouldReceive('translate')
            ->with('qanda.bilaterals.third-country.no-blurb')
            ->andReturn($thirdCountryNoBlurb);

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
                $thirdCountryNoBlurb,
                $yesNo,
                'qanda.bilaterals.third-country.not-selected-message'
            )
            ->once();

        $thirdCountryFieldsetPopulator = new ThirdCountryFieldsetPopulator(
            $translator,
            $yesNoWithMarkupForNoPopulator,
            $standardYesNoValueOptionsGenerator
        );

        $thirdCountryFieldsetPopulator->populate($form, $fieldset, $options);
    }
}
