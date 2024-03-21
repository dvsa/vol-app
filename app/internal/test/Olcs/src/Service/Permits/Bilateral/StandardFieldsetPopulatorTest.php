<?php

/**
 * StandardFieldsetPopulator Test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace OlcsTest\Service\Permits\Bilateral;

use Olcs\Form\Element\Permits\BilateralNoOfPermitsCombinedTotalElement;
use Olcs\Form\Element\Permits\BilateralNoOfPermitsElement;
use Olcs\Service\Permits\Bilateral\StandardFieldsetPopulator;
use Olcs\Service\Permits\Bilateral\NoOfPermitsElementGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Laminas\Form\Fieldset;

/**
 * StandardFieldsetPopulator Test
 *
 * @author Jonathan Thomas <jonthan@opalise.co.uk>
 */
class StandardFieldsetPopulatorTest extends TestCase
{
    public function testPopulate()
    {
        $field1 = [
            'cabotage' => 'field1cabotage',
            'journey' => 'field1journey',
            'value' => 'field1value'
        ];

        $field2 = [
            'cabotage' => 'field2cabotage',
            'journey' => 'field2journey',
            'value' => 'field2value'
        ];

        $fields = [
            $field1,
            $field2
        ];

        $bilateralNoOfPermitsCombinedTotalElementParams = [
            'type' => BilateralNoOfPermitsCombinedTotalElement::class,
            'name' => 'combinedTotal'
        ];

        $fieldset = m::mock(Fieldset::class);
        $noOfPermitsElement1 = m::mock(BilateralNoOfPermitsElement::class);
        $noOfPermitsElement2 = m::mock(BilateralNoOfPermitsElement::class);

        $fieldset->shouldReceive('add')
            ->with($bilateralNoOfPermitsCombinedTotalElementParams)
            ->once()
            ->ordered();
        $fieldset->shouldReceive('add')
            ->with($noOfPermitsElement1)
            ->once()
            ->ordered();
        $fieldset->shouldReceive('add')
            ->with($noOfPermitsElement2)
            ->once()
            ->ordered();

        $noOfPermitsElementGenerator = m::mock(NoOfPermitsElementGenerator::class);
        $noOfPermitsElementGenerator->shouldReceive('generate')
            ->with($field1)
            ->andReturn($noOfPermitsElement1);
        $noOfPermitsElementGenerator->shouldReceive('generate')
            ->with($field2)
            ->andReturn($noOfPermitsElement2);

        $standardFieldsetPopulator = new StandardFieldsetPopulator($noOfPermitsElementGenerator);

        $standardFieldsetPopulator->populate($fieldset, $fields);
    }
}
