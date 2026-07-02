<?php

namespace CommonTest\Service\Qa\Custom\Bilateral;

use Common\Service\Qa\Custom\Bilateral\Radio;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * RadioTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class RadioTest extends MockeryTestCase
{
    public function testGetInputSpecification(): void
    {
        $radio = m::mock(Radio::class)->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $parentInputSpecification = [
            'required' => true,
            'key1' => [
                'foo1' => 'bar1',
                'foo2' => 'bar2',
            ],
        ];

        $radio->shouldReceive('callParentGetInputSpecification')
            ->andReturn($parentInputSpecification);

        $expectedInputSpecification = [
            'required' => false,
            'key1' => [
                'foo1' => 'bar1',
                'foo2' => 'bar2',
            ],
        ];

        $this->assertEquals(
            $expectedInputSpecification,
            $radio->getInputSpecification()
        );
    }
}
