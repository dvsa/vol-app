<?php

/**
 * Fieldset Populator test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace CommonTest\Service\Qa;

use Common\Service\Qa\FieldsetAdder;
use Common\Service\Qa\FieldsetPopulator;
use Common\Service\Qa\UsageContext;
use Common\Service\Qa\ValidatorsAdder;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Fieldset;

/**
 * Fieldset Populator Test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class FieldsetPopulatorTest extends MockeryTestCase
{
    /**
     * @dataProvider dpTestPopulate
     */
    public function testPopulate($usageContext): void
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
            ->with($fieldset, $applicationStep1, $usageContext)
            ->once()
            ->globally()
            ->ordered();
        $fieldsetAdder->shouldReceive('add')
            ->with($fieldset, $applicationStep2, $usageContext)
            ->once()
            ->globally()
            ->ordered();

        $validatorsAdder = m::mock(ValidatorsAdder::class);
        $validatorsAdder->shouldReceive('add')
            ->with($fieldset, $applicationStep1)
            ->once()
            ->globally()
            ->ordered();
        $validatorsAdder->shouldReceive('add')
            ->with($fieldset, $applicationStep2)
            ->once()
            ->globally()
            ->ordered();

        $fieldsetPopulator = new FieldsetPopulator($fieldsetAdder, $validatorsAdder);
        $fieldsetPopulator->populate($fieldset, $applicationSteps, $usageContext);
    }

    /**
     * @return string[][]
     *
     * @psalm-return list{list{'USAGE_CONTEXT_SELFSERVE'}, list{'USAGE_CONTEXT_INTERNAL'}}
     */
    public function dpTestPopulate(): array
    {
        return [
            [UsageContext::CONTEXT_SELFSERVE],
            [UsageContext::CONTEXT_INTERNAL],
        ];
    }
}
