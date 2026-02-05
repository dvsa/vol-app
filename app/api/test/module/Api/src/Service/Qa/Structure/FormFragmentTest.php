<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure;

use Dvsa\Olcs\Api\Service\Qa\Structure\ApplicationStep;
use Dvsa\Olcs\Api\Service\Qa\Structure\FormFragment;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * FormFragmentTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class FormFragmentTest extends MockeryTestCase
{
    public function testGetRepresentation(): void
    {
        $applicationStep1Representation = [
            'applicationStep1Key1' => 'applicationStep1Value1',
            'applicationStep1Key2' => 'applicationStep2Value2',
        ];
        $applicationStep1 = m::mock(ApplicationStep::class);
        $applicationStep1->shouldReceive('getRepresentation')
            ->andReturn($applicationStep1Representation);

        $applicationStep2Representation = [
            'applicationStep2Key1' => 'applicationStep2Value1',
            'applicationStep2Key2' => 'applicationStep2Value2',
        ];
        $applicationStep2 = m::mock(ApplicationStep::class);
        $applicationStep2->shouldReceive('getRepresentation')
            ->andReturn($applicationStep2Representation);

        $formFragment = new FormFragment();
        $formFragment->addApplicationStep($applicationStep1);
        $formFragment->addApplicationStep($applicationStep2);

        $expectedRepresentation = [
            $applicationStep1Representation,
            $applicationStep2Representation
        ];

        $this->assertEquals(
            $expectedRepresentation,
            $formFragment->getRepresentation()
        );
    }
}
