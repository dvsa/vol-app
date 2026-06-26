<?php

namespace CommonTest\Service\Qa\Custom\Bilateral;

use Common\Service\Qa\Custom\Bilateral\Radio;
use Common\Service\Qa\Custom\Bilateral\StandardAndCabotageYesNoRadio;
use Common\Service\Qa\Custom\Bilateral\StandardAndCabotageYesNoRadioValidator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * StandardAndCabotageYesNoRadioTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class StandardAndCabotageYesNoRadioTest extends MockeryTestCase
{
    public function testGetInputSpecification(): void
    {
        $standardAndCabotageYesNoRadio = m::mock(StandardAndCabotageYesNoRadio::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $standardAndCabotageYesNoRadio->setOption('yesContentElement', m::mock(Radio::class));

        $parentInputSpecificationKey1 = [
            'foo1' => 'bar1',
            'foo2' => 'bar2',
        ];

        $parentInputSpecificationValidator1 = [
            'key1' => 'value1',
            'key2' => 'value2',
        ];

        $parentInputSpecification = [
            'key1' => $parentInputSpecificationKey1,
            'validators' => [
                $parentInputSpecificationValidator1
            ]
        ];

        $standardAndCabotageYesNoRadio->shouldReceive('callParentGetInputSpecification')
            ->andReturn($parentInputSpecification);

        $inputSpecification = $standardAndCabotageYesNoRadio->getInputSpecification();
        $this->assertCount(2, $inputSpecification);
        $this->assertArrayHasKey('key1', $inputSpecification);
        $this->assertArrayHasKey('validators', $inputSpecification);
        $this->assertEquals($parentInputSpecificationKey1, $inputSpecification['key1']);

        $inputSpecificationValidators = $inputSpecification['validators'];
        $this->assertCount(2, $inputSpecificationValidators);
        $this->assertArrayHasKey(0, $inputSpecificationValidators);
        $this->assertEquals($parentInputSpecificationValidator1, $inputSpecificationValidators[0]);
        $this->assertArrayHasKey(1, $inputSpecificationValidators);
        $this->assertInstanceOf(StandardAndCabotageYesNoRadioValidator::class, $inputSpecificationValidators[1]);
    }
}
