<?php

namespace CommonTest\Form\Elements\Custom;

use Common\Form\Elements\Custom\VehicleVrm;
use Dvsa\Olcs\Transfer\Validators\Vrm;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Validator\NotEmpty;

/**
 * @covers \Common\Form\Elements\Custom\VehicleVrm
 */
class VehicleVrmTest extends MockeryTestCase
{
    public function testValidators(): void
    {
        /** @var VehicleVrm $sut */
        $sut = m::mock(VehicleVrm::class)->makePartial()
            ->shouldReceive('getName')->once()->andReturn('unit_Name')
            ->getMock();

        $actual = $sut->getInputSpecification();

        static::assertEquals('unit_Name', $actual['name']);
        static::assertTrue($actual['required']);

        $notEmptyValidator = current($actual['validators']);
        static::assertSame(NotEmpty::class, $notEmptyValidator['name']);
        static::assertTrue($notEmptyValidator['break_chain_on_failure']);
        static::assertSame(
            [
                'messages' => [
                    NotEmpty::IS_EMPTY => 'licence.vehicle.add.search.vrm-missing'
                ]
            ],
            $notEmptyValidator['options']
        );

        $vrmValidator = next($actual['validators']);
        static::assertSame(Vrm::class, $vrmValidator['name']);
    }
}
