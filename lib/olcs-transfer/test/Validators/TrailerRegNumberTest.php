<?php

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\TrailerRegNumber;
use PHPUnit\Framework\TestCase;

class TrailerRegNumberTest extends TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new TrailerRegNumber();
    }

    /**
     * @dataProvider dpIsValid
     */
    public function testIsValid($value, $isValid)
    {
        $this->assertEquals($isValid, $this->sut->isValid($value));
    }

    public function dpIsValid()
    {
        return [
            'valid - uppercase' => ['A1234567', true],
            'valid - lowercase' => ['a1234567', true],
            'invalid - no spaces' => ['A 1234567', false],
            'invalid - must start with 1 letter' => ['01234567', false],
            'invalid - a letter must be followed by exactly 7 digits (not 6)' => ['A123456', false],
            'invalid - a letter must be followed by exactly 7 digits (not 8)' => ['A12345678', false],
        ];
    }
}
