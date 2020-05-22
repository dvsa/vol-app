<?php

/**
 * PeriodFieldsetGenerator Test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
namespace OlcsTest\Service\Permits\Bilateral;

use Olcs\Form\Element\Permits\BilateralNoOfPermitsCombinedTotalElement;
use Olcs\Form\Element\Permits\BilateralNoOfPermitsElement;
use Olcs\Service\Permits\Bilateral\PeriodFieldsetGenerator;
use Olcs\Service\Permits\Bilateral\NoOfPermitsElementGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Zend\Form\Factory as FormFactory;
use Zend\Form\Fieldset;

/**
 * PeriodFieldsetGenerator Test
 *
 * @author Jonathan Thomas <jonthan@opalise.co.uk>
 */
class PeriodFieldsetGeneratorTest extends TestCase
{
    public function testGenerate()
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

        $periodData = [
            'id' => 45,
            'fields' => [
                $field1,
                $field2
            ]
        ];

        $periodFieldsetParams = [
            'type' => Fieldset::class,
            'name' => 'period45',
            'attributes' => [
                'id' => 'period45',
                'data-role' => 'period',
            ]
        ];

        $bilateralNoOfPermitsCombinedTotalElementParams = [
            'type' => BilateralNoOfPermitsCombinedTotalElement::class,
            'name' => 'combinedTotal'
        ];

        $periodFieldset = m::mock(Fieldset::class);
        $noOfPermitsElement1 = m::mock(BilateralNoOfPermitsElement::class);
        $noOfPermitsElement2 = m::mock(BilateralNoOfPermitsElement::class);

        $periodFieldset->shouldReceive('add')
            ->with($bilateralNoOfPermitsCombinedTotalElementParams)
            ->once()
            ->ordered();
        $periodFieldset->shouldReceive('add')
            ->with($noOfPermitsElement1)
            ->once()
            ->ordered();
        $periodFieldset->shouldReceive('add')
            ->with($noOfPermitsElement2)
            ->once()
            ->ordered();

        $formFactory = m::mock(FormFactory::class);
        $formFactory->shouldReceive('create')
            ->with($periodFieldsetParams)
            ->andReturn($periodFieldset);

        $noOfPermitsElementGenerator = m::mock(NoOfPermitsElementGenerator::class);
        $noOfPermitsElementGenerator->shouldReceive('generate')
            ->with($field1)
            ->andReturn($noOfPermitsElement1);
        $noOfPermitsElementGenerator->shouldReceive('generate')
            ->with($field2)
            ->andReturn($noOfPermitsElement2);

        $periodFieldsetGenerator = new PeriodFieldsetGenerator($formFactory, $noOfPermitsElementGenerator);

        $this->assertSame(
            $periodFieldset,
            $periodFieldsetGenerator->generate($periodData)
        );
    }
}
