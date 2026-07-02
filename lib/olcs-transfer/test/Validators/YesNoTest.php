<?php

/**
 * YesNoTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\YesNo;

/**
 * YesNoTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class YesNoTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new YesNo();
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
            ['N', true],
            ['Y', true],
            ['n', false],
            ['y', false],
            ['', false],
            ['A', false],
            ['B', false],
            ['Q', false],
            [null, false],
        ];
    }
}
