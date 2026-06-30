<?php

namespace CommonTest\Validator;

use Common\Validator\Date;

/**
 * Date Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DateTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Date
     */
    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new Date();
    }

    /**
     * @dataProvider provideIsValid
     */
    public function testIsValid($input, $expected): void
    {
        $this->assertEquals($expected, $this->sut->isValid($input));
    }

    /**
     * @return array
     */
    public function provideIsValid()
    {
        return [
            [null, true],
            ['2015-01-02', true],
            ['2015-01-aa', false],
            ['2015-aa-02', false],
            ['aaaa-01-02', false],
            ['98-01-02', false],
            ['98-aa-02', false],
        ];
    }
}
