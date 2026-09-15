<?php

declare(strict_types=1);

namespace OlcsTest\Validator;

use Olcs\Validator\TypeOfPI;

/**
 * Class TypeOfPITest
 * @package OlcsTest\Validator
 */
final class TypeOfPITest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param $expected
     * @param $value
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideIsValid')]
    public function testIsValid(mixed $expected, mixed $value): void
    {
        $sut = new TypeOfPI();

        $this->assertEquals($expected, $sut->isValid($value));
    }

    public static function provideIsValid(): \Iterator
    {
        yield [true, ['test1']];
        yield [true, ['test', 'test2', 'test3']];
        yield [true, ['pi_t_tm_only']];
        yield [false, ['pi_t_tm_only', 'test2']];
    }
}
