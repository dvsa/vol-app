<?php

namespace CommonTest\Form\Elements\Custom;

use Common\Form\Elements\Custom\VehicleVrmAny;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Common\Form\Elements\Custom\VehicleVrmAny
 */
class VehicleVrmAnyTest extends MockeryTestCase
{
    public function testValidators(): void
    {
        /** @var VehicleVrmAny $sut */
        $sut = m::mock(VehicleVrmAny::class)->makePartial()
            ->shouldReceive('getName')->once()->andReturn('unit_Name')
            ->getMock();

        $actual = $sut->getInputSpecification();

        static::assertEquals('unit_Name', $actual['name']);
        static::assertTrue($actual['required']);
        static::assertInstanceOf(\Laminas\Filter\StringTrim::class, current($actual['filters']));

        static::assertEquals(
            [
                \Laminas\Validator\StringLength::class,
            ],
            array_map(
                static fn($item) => $item['name'],
                $actual['validators']
            )
        );
    }
}
