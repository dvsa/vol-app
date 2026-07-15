<?php

/**
 * YesNoTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\YesNo;

/**
 * YesNoTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class YesNoTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new YesNo();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderIsValid')]
    public function testIsValid($value, $expected)
    {
        $this->assertEquals($expected, $this->sut->isValid($value));
    }

    public static function dataProviderIsValid(): \Iterator
    {
        yield ['N', true];
        yield ['Y', true];
        yield ['n', false];
        yield ['y', false];
        yield ['', false];
        yield ['A', false];
        yield ['B', false];
        yield ['Q', false];
        yield [null, false];
    }
}
