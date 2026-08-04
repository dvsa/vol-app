<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\Order;

/**
 * OrderTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class OrderTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Order();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderIsValid')]
    public function testIsValid($value, $expected)
    {
        $this->assertEquals($expected, $this->sut->isValid($value));
    }

    public static function dataProviderIsValid(): \Iterator
    {
        yield ['asc', true];
        yield ['desc', true];
        yield ['asc,desc', true];
        yield ['desc, asc', true];
        yield ['ASC, DESC', true];
        yield ['ASC, DESC, ASC, DESC, ASC', true];
        yield ['ASCX', false];
        yield ['DESCX', false];
        yield ['X', false];
        yield ['ASC, desc, descX', false];
        yield ['', false];
        yield [null, false];
    }
}
