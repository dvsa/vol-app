<?php

namespace CommonTest\Service\Qa\Custom\Ecmt;

use Common\Service\Qa\Custom\Ecmt\InfoIconAdder;
use Common\Service\Qa\Custom\Ecmt\PermitUsageFieldsetPopulator;
use Common\Service\Qa\RadioFieldsetPopulator;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * PermitUsageFieldsetPopulatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class PermitUsageFieldsetPopulatorTest extends MockeryTestCase
{
    public function testPopulate(): void
    {
        $options = [
            'key1' => 'value1',
            'key2' => 'value2'
        ];

        $form = m::mock(Form::class);

        $fieldset = m::mock(Fieldset::class);

        $radioFieldsetPopulator = m::mock(RadioFieldsetPopulator::class);
        $radioFieldsetPopulator->shouldReceive('populate')
            ->with($form, $fieldset, $options)
            ->once()
            ->globally()
            ->ordered();

        $infoIconAdder = m::mock(InfoIconAdder::class);
        $infoIconAdder->shouldReceive('add')
            ->with($fieldset, 'qanda.ecmt.permit-usage.footer-annotation')
            ->once()
            ->globally()
            ->ordered();

        $permitUsageFieldsetPopulator = new PermitUsageFieldsetPopulator($radioFieldsetPopulator, $infoIconAdder);

        $permitUsageFieldsetPopulator->populate($form, $fieldset, $options);
    }
}
