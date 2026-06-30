<?php

namespace CommonTest\Service\Qa\Custom\Bilateral;

use Common\Service\Helper\TranslationHelperService;
use Common\Service\Qa\Custom\Bilateral\EmissionsStandardsFieldsetPopulator;
use Common\Service\Qa\Custom\Bilateral\YesNoValueOptionsGenerator;
use Common\Service\Qa\Custom\Bilateral\YesNoWithMarkupForNoPopulator;
use Common\Service\Qa\Custom\Common\WarningAdder;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;

/**
 * EmissionsStandardsFieldsetPopulatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class EmissionsStandardsFieldsetPopulatorTest extends MockeryTestCase
{
    public function testPopulate(): void
    {
        $emissionsStandardsNoBlurb = 'Emissions standards no blurb';

        $yesNo = 'yesNo';

        $options = [
            'yesNo' => $yesNo
        ];

        $fieldset = m::mock(Fieldset::class);

        $form = m::mock(Form::class);

        $warningAdder = m::mock(WarningAdder::class);
        $warningAdder->shouldReceive('add')
            ->with($fieldset, 'qanda.bilaterals.emissions-standards.euro2-warning')
            ->once()
            ->globally()
            ->ordered();

        $translator = m::mock(TranslationHelperService::class);
        $translator->shouldReceive('translate')
            ->with('qanda.bilaterals.emissions-standards.no-blurb')
            ->andReturn($emissionsStandardsNoBlurb);

        $yesNoValueOptions = [
            'key1' => 'value1',
            'key2' => 'value2'
        ];

        $yesNoValueOptionsGenerator = m::mock(YesNoValueOptionsGenerator::class);
        $yesNoValueOptionsGenerator->shouldReceive('generate')
            ->with(
                'qanda.bilaterals.emissions-standards.euro3-or-euro4',
                'qanda.bilaterals.emissions-standards.euro5-euro6-or-higher'
            )
            ->andReturn($yesNoValueOptions);

        $yesNoWithMarkupForNoPopulator = m::mock(YesNoWithMarkupForNoPopulator::class);
        $yesNoWithMarkupForNoPopulator->shouldReceive('populate')
            ->with(
                $fieldset,
                $yesNoValueOptions,
                $emissionsStandardsNoBlurb,
                $yesNo,
                'qanda.bilaterals.emissions-standards.not-selected-message'
            )
            ->once()
            ->globally()
            ->ordered();

        $emissionsStandardsFieldsetPopulator = new EmissionsStandardsFieldsetPopulator(
            $warningAdder,
            $translator,
            $yesNoWithMarkupForNoPopulator,
            $yesNoValueOptionsGenerator
        );

        $emissionsStandardsFieldsetPopulator->populate($form, $fieldset, $options);
    }
}
