<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\ApplicationStatus;

/**
 * ApplicationStatus Valitador Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class ApplicationStatusTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new ApplicationStatus();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('isValidProvider')]
    public function testIsValid($value, $expected)
    {
        $this->assertEquals($expected, $this->sut->isValid($value));
    }

    public static function isValidProvider(): \Iterator
    {
        yield ['apsts_not_submitted', true];
        yield ['apsts_granted', true];
        yield ['apsts_consideration', true];
        yield ['apsts_valid', true];
        yield ['apsts_withdrawn', true];
        yield ['apsts_refused', true];
        yield ['apsts_ntu', true];
        yield ['apsts_curtailed', true];
        yield ['foobar', false];
        yield [1, false];
        yield [' ', false];
        yield [null, false];
    }
}
