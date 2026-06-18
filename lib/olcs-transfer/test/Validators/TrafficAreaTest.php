<?php

namespace Dvsa\OlcsTest\Transfer\Validators;

use Dvsa\Olcs\Transfer\Validators\TrafficArea;

/**
 * @author Mat Evans <mat.evans@valtech.co.uk>
 * @covers \Dvsa\Olcs\Transfer\Validators\TrafficArea
 */
class TrafficAreaTest extends \PHPUnit\Framework\TestCase
{
    /** @var TrafficArea */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new TrafficArea();

        $this->sut->setExtraHaystack(['extra_1']);
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
            ['B', true],
            ['C', true],
            ['D', true],
            ['F', true],
            ['G', true],
            ['H', true],
            ['K', true],
            ['M', true],
            ['N', true],
            ['a', false],
            ['A', false],
            ['E', false],
            ['I', false],
            ['J', false],
            ['L', false],
            ['O', false],
            ['b', false],
            ['c', false],
            [1, false],
            [' ', false],
            [null, false],
            //  extra haystack
            ['extra_1', true],
            ['extra_3', false],
        ];
    }
}
