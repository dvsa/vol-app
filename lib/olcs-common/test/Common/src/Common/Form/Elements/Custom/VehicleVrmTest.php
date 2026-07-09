<?php

declare(strict_types=1);

namespace CommonTest\Form\Elements\Custom;

use Common\Form\Elements\Custom\VehicleVrm;
use Dvsa\Olcs\Transfer\Validators\Vrm;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Validator\NotEmpty;

#[\PHPUnit\Framework\Attributes\CoversClass(\Common\Form\Elements\Custom\VehicleVrm::class)]
final class VehicleVrmTest extends MockeryTestCase
{
    public function testValidators(): void
    {
        /** @var VehicleVrm $sut */
        $sut = m::mock(VehicleVrm::class)->makePartial()
            ->shouldReceive('getName')->once()->andReturn('unit_Name')
            ->getMock();

        $actual = $sut->getInputSpecification();

        $this->assertEquals('unit_Name', $actual['name']);
        $this->assertTrue($actual['required']);

        $notEmptyValidator = current($actual['validators']);
        $this->assertSame(NotEmpty::class, $notEmptyValidator['name']);
        $this->assertTrue($notEmptyValidator['break_chain_on_failure']);
        $this->assertSame([
            'messages' => [
                NotEmpty::IS_EMPTY => 'licence.vehicle.add.search.vrm-missing'
            ]
        ], $notEmptyValidator['options']);

        $vrmValidator = next($actual['validators']);
        $this->assertSame(Vrm::class, $vrmValidator['name']);
    }
}
