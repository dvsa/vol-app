<?php

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\Order;

/**
 * OrderTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class OrderTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Order();
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
            ['asc', true],
            ['desc', true],
            ['asc,desc', true],
            ['desc, asc', true],
            ['ASC, DESC', true],
            ['ASC, DESC, ASC, DESC, ASC', true],
            ['ASCX', false],
            ['DESCX', false],
            ['X', false],
            ['ASC, desc, descX', false],
            ['', false],
            [null, false],
        ];
    }
}
