<?php

namespace CommonTest\Service\Qa\FieldsetModifier;

use Common\Service\Qa\FieldsetModifier\FieldsetModifier;
use Common\Service\Qa\FieldsetModifier\FieldsetModifierInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Fieldset;

/**
 * FieldsetModifierTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class FieldsetModifierTest extends MockeryTestCase
{
    public function testModify(): void
    {
        $fieldset = m::mock(Fieldset::class);

        $fieldsetModifierImplementation1 = m::mock(FieldsetModifierInterface::class);
        $fieldsetModifierImplementation1->shouldReceive('shouldModify')
            ->with($fieldset)
            ->andReturn(true);
        $fieldsetModifierImplementation1->shouldReceive('modify')
            ->with($fieldset)
            ->once()
            ->globally()
            ->ordered();

        $fieldsetModifierImplementation2 = m::mock(FieldsetModifierInterface::class);
        $fieldsetModifierImplementation2->shouldReceive('shouldModify')
            ->with($fieldset)
            ->andReturn(false);

        $fieldsetModifierImplementation3 = m::mock(FieldsetModifierInterface::class);
        $fieldsetModifierImplementation3->shouldReceive('shouldModify')
            ->with($fieldset)
            ->andReturn(true);
        $fieldsetModifierImplementation3->shouldReceive('modify')
            ->with($fieldset)
            ->once()
            ->globally()
            ->ordered();

        $fieldsetModifier = new FieldsetModifier();
        $fieldsetModifier->registerModifier($fieldsetModifierImplementation1);
        $fieldsetModifier->registerModifier($fieldsetModifierImplementation2);
        $fieldsetModifier->registerModifier($fieldsetModifierImplementation3);

        $fieldsetModifier->modify($fieldset);
    }
}
