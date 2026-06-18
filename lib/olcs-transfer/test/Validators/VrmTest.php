<?php

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\Vrm;
use PHPUnit\Framework\TestCase;

class VrmTest extends TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Vrm();
    }

    /**
     * @dataProvider isValidProvider
     */
    public function testIsValid(string $value, bool $isValid)
    {
        $this->assertEquals($isValid, $this->sut->isValid($value));
    }

    public function isValidProvider()
    {
        return [
            'Matches defined Pattern' => [
                'BP10ABC', true
            ],
            'Catches exceptional VRM #1' => [
                '11', true
            ],
            'Invalid Pattern' => [
                'SH7SSSD', false
            ],
        ];
    }
}
