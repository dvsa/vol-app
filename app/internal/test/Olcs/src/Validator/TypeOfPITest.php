<?php

namespace OlcsTest\Validator;

use Olcs\Validator\TypeOfPI;

/**
 * Class TypeOfPITest
 * @package OlcsTest\Validator
 */
class TypeOfPITest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider provideIsValid
     * @param $expected
     * @param $value
     */
    public function testIsValid($expected, $value)
    {
        $sut = new TypeOfPI();

        $this->assertEquals($expected, $sut->isValid($value));
    }

    public function provideIsValid()
    {
        return [
            [true, ['test1']],
            [true, ['test', 'test2', 'test3']],
            [true, ['pi_t_tm_only']],
            [false, ['pi_t_tm_only', 'test2']],
        ];
    }
}
