<?php

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\LicenceStatus;

/**
 * LicenceStatusTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class LicenceStatusTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new LicenceStatus();
    }

    /**
     * @dataProvider isValidProvider
     */
    public function testIsValid($value, $expected)
    {
        $this->assertEquals($expected, $this->sut->isValid($value));
    }

    public function isValidProvider()
    {
        return [
            ['lsts_consideration', true],
            ['lsts_not_submitted', true],
            ['lsts_suspended', true],
            ['lsts_valid', true],
            ['lsts_curtailed', true],
            ['lsts_granted', true],
            ['lsts_surrendered', true],
            ['lsts_withdrawn', true],
            ['lsts_refused', true],
            ['lsts_revoked', true],
            ['lsts_ntu', true],
            ['lsts_terminated', true],
            ['lsts_cns', true],
            ['LSTS_cns', false],
            ['foobar', false],
            [1, false],
            [' ', false],
            [null, false],
        ];
    }
}
