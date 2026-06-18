<?php

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\Title;

/**
 * TitleTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TitleTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Title();
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
            'Dr' => ['title_dr', true],
            'Miss' => ['title_miss', true],
            'Mr' => ['title_mr', true],
            'Mrs' => ['title_mrs', true],
            'Ms' => ['title_ms', true],
            'uppercase' => ['TITLE_DR', false],
            'random' => ['foobar', false],
            'number' => [1, false],
            'space' => [' ', false],
            'null' => [null, false],
        ];
    }
}
