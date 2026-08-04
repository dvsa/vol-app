<?php

declare(strict_types=1);

namespace CommonTest\Form\Elements\Custom;

use Common\Form\Elements\Custom\VehicleVrmAny;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Common\Form\Elements\Custom\VehicleVrmAny::class)]
final class VehicleVrmAnyTest extends MockeryTestCase
{
    public function testValidators(): void
    {
        /** @var VehicleVrmAny $sut */
        $sut = m::mock(VehicleVrmAny::class)->makePartial()
            ->shouldReceive('getName')->once()->andReturn('unit_Name')
            ->getMock();

        $actual = $sut->getInputSpecification();

        $this->assertEquals('unit_Name', $actual['name']);
        $this->assertTrue($actual['required']);
        $this->assertInstanceOf(\Laminas\Filter\StringTrim::class, current($actual['filters']));

        $this->assertSame([
            \Laminas\Validator\StringLength::class,
        ], array_map(
            static fn($item) => $item['name'],
            $actual['validators']
        ));
    }
}
