<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\TrailerRegNumber;
use PHPUnit\Framework\TestCase;

final class TrailerRegNumberTest extends TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new TrailerRegNumber();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsValid')]
    public function testIsValid($value, $isValid)
    {
        $this->assertEquals($isValid, $this->sut->isValid($value));
    }

    public static function dpIsValid(): \Iterator
    {
        yield 'valid - uppercase' => ['A1234567', true];
        yield 'valid - lowercase' => ['a1234567', true];
        yield 'invalid - no spaces' => ['A 1234567', false];
        yield 'invalid - must start with 1 letter' => ['01234567', false];
        yield 'invalid - a letter must be followed by exactly 7 digits (not 6)' => ['A123456', false];
        yield 'invalid - a letter must be followed by exactly 7 digits (not 8)' => ['A12345678', false];
    }
}
