<?php

/**
 * Fieldset Populator test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
namespace OlcsTest\Service\Qa;

use Common\Service\Qa\FieldsetAdder;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Qa\FieldsetPopulator;
use Zend\Form\Fieldset;

/**
 * Fieldset Populator Test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class FieldsetPopulatorTest extends MockeryTestCase
{
    public function testPopulate()
    {
        $applicationStep1 = [
            'step1Attribute1' => 'step1Value1',
            'step1Attribute2' => 'step1Value2'
        ];

        $applicationStep2 = [
            'step2Attribute1' => 'step2Value1',
            'step2Attribute2' => 'step2Value2'
        ];

        $applicationSteps = [
            $applicationStep1,
            $applicationStep2
        ];

        $fieldset = m::mock(Fieldset::class);

        $fieldsetAdder = m::mock(FieldsetAdder::class);
        $fieldsetAdder->shouldReceive('add')
            ->with($fieldset, $applicationStep1)
            ->once()
            ->ordered();
        $fieldsetAdder->shouldReceive('add')
            ->with($fieldset, $applicationStep2)
            ->once()
            ->ordered();

        $fieldsetPopulator = new FieldsetPopulator($fieldsetAdder);
        $fieldsetPopulator->populate($fieldset, $applicationSteps);
    }
}
