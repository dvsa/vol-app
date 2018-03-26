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
    public function testIsValid($value, $totalAuthVehicles, $expected)
    {
        $this->assertEquals($expected, $this->sut->isValid($value, $totalAuthVehicles));
    }

    public function isValidProvider()
    {
        return [
            [6, ['totAuthVehicles' => 5], false],
            [4, ['totAuthVehicles' => 2], false],
            [3, ['totAuthVehicles' => 0], false],
            [4, ['totAuthVehicles' => 5], true],
            [10, ['totAuthVehicles' => 10], true],
            [4, ['totAuthVehicles' => 0], false],
            [0, ['totAuthVehicles' => 0], true],
            [0, ['totAuthVehicles' => 7], true],
        ];
    }
}
