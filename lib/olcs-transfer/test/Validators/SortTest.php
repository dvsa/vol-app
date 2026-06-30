<?php

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\Sort;

class SortTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Sort();
    }

    /**
     * @dataProvider dataProviderIsValid
     */
    public function testIsValid($value, $expected)
    {
        $this->assertEquals($expected, $this->sut->isValid($value));
    }

    public function dataProviderIsValid()
    {
        return [
            ['field', true],
            ['table.field', true],
            ['field_name', true],
            ['table.field-name', true],
            ['table_name.field-name', true],
            ['table.name; SELECT something FROM table', false],
            ['any invalid character (', false],
            ['""', false],
            ['SELECT *', false]
        ];
    }
}
