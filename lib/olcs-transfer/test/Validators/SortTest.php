<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\Sort;

final class SortTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Sort();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderIsValid')]
    public function testIsValid($value, $expected)
    {
        $this->assertEquals($expected, $this->sut->isValid($value));
    }

    public static function dataProviderIsValid(): \Iterator
    {
        yield ['field', true];
        yield ['table.field', true];
        yield ['field_name', true];
        yield ['table.field-name', true];
        yield ['table_name.field-name', true];
        yield ['table.name; SELECT something FROM table', false];
        yield ['any invalid character (', false];
        yield ['""', false];
        yield ['SELECT *', false];
    }
}
