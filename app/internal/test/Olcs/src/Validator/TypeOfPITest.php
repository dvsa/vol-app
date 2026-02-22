<?php

declare(strict_types=1);

namespace OlcsTest\Validator;

use Olcs\Validator\TypeOfPI;

/**
 * Class TypeOfPITest
 * @package OlcsTest\Validator
 */
class TypeOfPITest extends \PHPUnit\Framework\TestCase
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

    public static function provideIsValid(): array
    {
        return [
            [true, ['test1']],
            [true, ['test', 'test2', 'test3']],
            [true, ['pi_t_tm_only']],
            [false, ['pi_t_tm_only', 'test2']],
        ];
    }
}
