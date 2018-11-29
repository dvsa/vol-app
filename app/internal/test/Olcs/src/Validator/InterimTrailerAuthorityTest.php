<?php

namespace OlcsTest\Validator;

use Olcs\Validator\InterimTrailerAuthority;

class InterimTrailerAuthorityTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    public function setUp()
    {
        $this->sut = new InterimTrailerAuthority();
    }

    /**
     * @dataProvider isValidProvider
     */
    public function testIsValid($value, $totalAuthTrailers, $expected)
    {
        $this->assertEquals($expected, $this->sut->isValid($value, $totalAuthTrailers));
    }

    public function isValidProvider()
    {
        return [
            [6, ['totAuthTrailers' => 5], false],
            [6, ['totAuthTrailers' => 7], true],
            [5, ['totAuthTrailers' => 5], true],
            [0, ['totAuthTrailers' => 0], true],
            [1, ['totAuthTrailers' => 0], false],
            [1, ['totAuthTrailers' => 10], true],
            [0, ['totAuthTrailers' => 10], true],
        ];
    }
}
