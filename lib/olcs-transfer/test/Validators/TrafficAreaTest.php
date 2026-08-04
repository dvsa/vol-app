<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\TrafficArea;

/**
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Validators\TrafficArea::class)]
final class TrafficAreaTest extends \PHPUnit\Framework\TestCase
{
    /** @var TrafficArea */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new TrafficArea();

        $this->sut->setExtraHaystack(['extra_1']);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('isValidProvider')]
    public function testIsValid($value, $expected)
    {
        $this->assertEquals($expected, $this->sut->isValid($value));
    }

    public static function isValidProvider(): \Iterator
    {
        yield ['B', true];
        yield ['C', true];
        yield ['D', true];
        yield ['F', true];
        yield ['G', true];
        yield ['H', true];
        yield ['K', true];
        yield ['M', true];
        yield ['N', true];
        yield ['a', false];
        yield ['A', false];
        yield ['E', false];
        yield ['I', false];
        yield ['J', false];
        yield ['L', false];
        yield ['O', false];
        yield ['b', false];
        yield ['c', false];
        yield [1, false];
        yield [' ', false];
        yield [null, false];
        //  extra haystack
        yield ['extra_1', true];
        yield ['extra_3', false];
    }
}
