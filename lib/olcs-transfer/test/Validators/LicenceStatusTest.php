<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\LicenceStatus;

/**
 * LicenceStatusTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class LicenceStatusTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new LicenceStatus();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('isValidProvider')]
    public function testIsValid($value, $expected)
    {
        $this->assertEquals($expected, $this->sut->isValid($value));
    }

    public static function isValidProvider(): \Iterator
    {
        yield ['lsts_consideration', true];
        yield ['lsts_not_submitted', true];
        yield ['lsts_suspended', true];
        yield ['lsts_valid', true];
        yield ['lsts_curtailed', true];
        yield ['lsts_granted', true];
        yield ['lsts_surrendered', true];
        yield ['lsts_withdrawn', true];
        yield ['lsts_refused', true];
        yield ['lsts_revoked', true];
        yield ['lsts_ntu', true];
        yield ['lsts_terminated', true];
        yield ['lsts_cns', true];
        yield ['LSTS_cns', false];
        yield ['foobar', false];
        yield [1, false];
        yield [' ', false];
        yield [null, false];
    }
}
