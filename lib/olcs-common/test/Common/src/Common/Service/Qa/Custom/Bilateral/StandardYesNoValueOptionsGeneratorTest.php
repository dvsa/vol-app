<?php

namespace CommonTest\Service\Qa\Custom\Bilateral;

use Common\Service\Qa\Custom\Bilateral\StandardYesNoValueOptionsGenerator;
use Common\Service\Qa\Custom\Bilateral\YesNoValueOptionsGenerator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * StandardYesNoValueOptionsGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class StandardYesNoValueOptionsGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $standardValueOptions = [
            'yes' => [
                'label' => 'Yes',
                'value' => 'Y',
            ],
            'no' => [
                'label' => 'No',
                'value' => 'N',
            ]
        ];

        $yesNoValueOptionsGenerator = m::mock(YesNoValueOptionsGenerator::class);
        $yesNoValueOptionsGenerator->shouldReceive('generate')
            ->with('Yes', 'No')
            ->andReturn($standardValueOptions);

        $standardYesNoValueOptionsGenerator = new StandardYesNoValueOptionsGenerator($yesNoValueOptionsGenerator);

        $this->assertEquals(
            $standardValueOptions,
            $standardYesNoValueOptionsGenerator->generate()
        );
    }
}
