<?php

namespace OlcsTest\Validator;

use PHPUnit_Framework_TestCase;
use Olcs\Validator\InterimVehicleAuthority;

class InterimVehicleAuthorityTest extends PHPUnit_Framework_TestCase
{
    protected $sut;

    public function setUp()
    {
        $this->sut = new InterimVehicleAuthority();
    }

    /**
     * @dataProvider isValidProvider
     */
    public function testIsValid($value, $formData, $expected)
    {
        $this->assertEquals($expected, $this->sut->isValid($value, $formData));
    }

    public function isValidProvider()
    {
        return [
            [6, ['totAuthVehicles' => 5, 'isVariation' => true], false],
            [4, ['totAuthVehicles' => 2, 'isVariation' => true], false],
            [3, ['totAuthVehicles' => 0, 'isVariation' => true], false],
            [4, ['totAuthVehicles' => 5, 'isVariation' => true], true],
            [10, ['totAuthVehicles' => 10, 'isVariation' => true], true],
            [4, ['totAuthVehicles' => 0, 'isVariation' => true], false],
            [0, ['totAuthVehicles' => 0, 'isVariation' => true], true],
            [0, ['totAuthVehicles' => 7, 'isVariation' => true], true],
            [0, ['totAuthVehicles' => 5, 'isVariation' => true], true],
            [0, ['totAuthVehicles' => 5, 'isVariation' => false], false],
            [0, ['totAuthVehicles' => 0, 'isVariation' => false], false],
            [0, ['totAuthVehicles' => 2, 'isVariation' => true], true],
        ];
    }
}
