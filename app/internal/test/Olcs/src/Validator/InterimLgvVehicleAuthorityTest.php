<?php

namespace OlcsTest\Validator;

use Olcs\Validator\InterimLgvVehicleAuthority;

class InterimLgvVehicleAuthorityTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new InterimLgvVehicleAuthority();
    }

    /**
     * @dataProvider isValidProvider
     */
    public function testIsValid($value, $formData, $expected, $expectedErrors)
    {
        $this->assertEquals($expected, $this->sut->isValid($value, $formData));
        $this->assertEquals($expectedErrors, $this->sut->getMessages());
    }

    public function isValidProvider()
    {
        return [
            [
                6,
                null,
                false,
                ['vehicleAuthExceeded' => 'The interim Light Goods Vehicle Authority cannot exceed the total Light Goods Vehicle Authority'],
            ],
            [
                6,
                [],
                false,
                ['vehicleAuthExceeded' => 'The interim Light Goods Vehicle Authority cannot exceed the total Light Goods Vehicle Authority'],
            ],
            [
                6,
                ['totAuthLgvVehicles' => 0],
                false,
                ['vehicleAuthExceeded' => 'The interim Light Goods Vehicle Authority cannot exceed the total Light Goods Vehicle Authority'],
            ],
            [
                6,
                ['totAuthLgvVehicles' => 5],
                false,
                ['vehicleAuthExceeded' => 'The interim Light Goods Vehicle Authority cannot exceed the total Light Goods Vehicle Authority'],
            ],
            [
                6,
                ['totAuthLgvVehicles' => 6, 'isVariation' => true],
                true,
                [],
            ],
            [
                6,
                ['totAuthLgvVehicles' => 10, 'isVariation' => true],
                true,
                [],
            ],
            [
                0,
                ['totAuthLgvVehicles' => 10, 'isVariation' => true],
                true,
                [],
            ],
            [
                0,
                ['totAuthLgvVehicles' => 10, 'isVariation' => false],
                false,
                ['valueBelowOne' => 'The input is not greater or equal than \'1\''],
            ],
            [
                0,
                ['totAuthLgvVehicles' => 0, 'isVariation' => true],
                true,
                [],
            ],
            [
                0,
                ['totAuthLgvVehicles' => 0, 'isVariation' => false],
                false,
                ['valueBelowOne' => 'The input is not greater or equal than \'1\''],
            ],
        ];
    }
}
