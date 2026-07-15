<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\Title;

/**
 * TitleTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class TitleTest extends \PHPUnit\Framework\TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Title();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('isValidProvider')]
    public function testIsValid($value, $expected)
    {
        $this->assertEquals($expected, $this->sut->isValid($value));
    }

    public static function isValidProvider(): \Iterator
    {
        yield 'Dr' => ['title_dr', true];
        yield 'Miss' => ['title_miss', true];
        yield 'Mr' => ['title_mr', true];
        yield 'Mrs' => ['title_mrs', true];
        yield 'Ms' => ['title_ms', true];
        yield 'uppercase' => ['TITLE_DR', false];
        yield 'random' => ['foobar', false];
        yield 'number' => [1, false];
        yield 'space' => [' ', false];
        yield 'null' => [null, false];
    }
}
