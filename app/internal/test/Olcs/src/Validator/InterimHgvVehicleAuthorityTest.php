<?php

namespace OlcsTest\Validator;

use Olcs\Validator\InterimHgvVehicleAuthority;

class InterimHgvVehicleAuthorityTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new InterimHgvVehicleAuthority();
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
                ['vehicleAuthExceeded' => 'The interim vehicle authority cannot exceed the total vehicle authority'],
            ],
            [
                6,
                [],
                false,
                ['vehicleAuthExceeded' => 'The interim vehicle authority cannot exceed the total vehicle authority'],
            ],
            [
                6,
                ['isEligibleForLgv' => true],
                false,
                ['hgvVehicleAuthExceeded' => 'The interim Heavy Goods Vehicle Authority cannot exceed the total Heavy Goods Vehicle Authority'],
            ],
            [
                6,
                ['totAuthHgvVehicles' => 0],
                false,
                ['vehicleAuthExceeded' => 'The interim vehicle authority cannot exceed the total vehicle authority'],
            ],
            [
                6,
                ['totAuthHgvVehicles' => 0, 'isEligibleForLgv' => true],
                false,
                ['hgvVehicleAuthExceeded' => 'The interim Heavy Goods Vehicle Authority cannot exceed the total Heavy Goods Vehicle Authority'],
            ],
            [
                6,
                ['totAuthHgvVehicles' => 5],
                false,
                ['vehicleAuthExceeded' => 'The interim vehicle authority cannot exceed the total vehicle authority'],
            ],
            [
                6,
                ['totAuthHgvVehicles' => 5, 'isEligibleForLgv' => true],
                false,
                ['hgvVehicleAuthExceeded' => 'The interim Heavy Goods Vehicle Authority cannot exceed the total Heavy Goods Vehicle Authority'],
            ],
            [
                6,
                ['totAuthHgvVehicles' => 6, 'isVariation' => true],
                true,
                [],
            ],
            [
                6,
                ['totAuthHgvVehicles' => 10, 'isVariation' => true],
                true,
                [],
            ],
            [
                0,
                ['totAuthHgvVehicles' => 10, 'isVariation' => true],
                true,
                [],
            ],
            [
                0,
                ['totAuthHgvVehicles' => 10, 'isVariation' => false],
                false,
                ['valueBelowOne' => 'The input is not greater or equal than \'1\''],
            ],
            [
                0,
                ['totAuthHgvVehicles' => 0, 'isVariation' => true],
                true,
                [],
            ],
            [
                0,
                ['totAuthHgvVehicles' => 0, 'isVariation' => false],
                false,
                ['valueBelowOne' => 'The input is not greater or equal than \'1\''],
            ],
        ];
    }
}
