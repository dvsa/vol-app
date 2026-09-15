<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\Vrm;
use PHPUnit\Framework\TestCase;

final class VrmTest extends TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Vrm();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('isValidProvider')]
    public function testIsValid(string $value, bool $isValid)
    {
        $this->assertEquals($isValid, $this->sut->isValid($value));
    }

    public static function isValidProvider(): \Iterator
    {
        yield 'Matches defined Pattern' => [
            'BP10ABC', true
        ];
        yield 'Catches exceptional VRM #1' => [
            '11', true
        ];
        yield 'Invalid Pattern' => [
            'SH7SSSD', false
        ];
    }
}
