<?php

namespace CommonTest\Service\Qa\Custom\Ecmt;

use Common\Service\Qa\Custom\Ecmt\InfoIconAdder;
use Common\Service\Qa\Custom\Ecmt\CheckEcmtNeededFieldsetPopulator;
use Common\Service\Qa\CheckboxFieldsetPopulator;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * CheckEcmtNeededFieldsetPopulatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class CheckEcmtNeededFieldsetPopulatorTest extends MockeryTestCase
{
    public function testPopulate(): void
    {
        $options = [
            'key1' => 'value1',
            'key2' => 'value2'
        ];

        $form = m::mock(Form::class);

        $fieldset = m::mock(Fieldset::class);

        $checkboxFieldsetPopulator = m::mock(CheckboxFieldsetPopulator::class);
        $checkboxFieldsetPopulator->shouldReceive('populate')
            ->with($form, $fieldset, $options)
            ->once()
            ->globally()
            ->ordered();

        $infoIconAdder = m::mock(InfoIconAdder::class);
        $infoIconAdder->shouldReceive('add')
            ->with($fieldset, 'qanda.ecmt.check-ecmt-needed.footer-annotation')
            ->once()
            ->globally()
            ->ordered();

        $checkEcmtNeededFieldsetPopulator = new CheckEcmtNeededFieldsetPopulator(
            $checkboxFieldsetPopulator,
            $infoIconAdder
        );

        $checkEcmtNeededFieldsetPopulator->populate($form, $fieldset, $options);
    }
}
