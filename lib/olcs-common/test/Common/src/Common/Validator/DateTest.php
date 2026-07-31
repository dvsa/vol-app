<?php

declare(strict_types=1);

namespace CommonTest\Validator;

use Common\Validator\Date;

/**
 * Date Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class DateTest extends \PHPUnit\Framework\TestCase
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

    #[\PHPUnit\Framework\Attributes\DataProvider('provideIsValid')]
    public function testIsValid($input, $expected): void
    {
        $this->assertEquals($expected, $this->sut->isValid($input));
    }

    /**
     * @return \Iterator<(int | string), mixed>
     */
    public static function provideIsValid(): \Iterator
    {
        yield [null, true];
        yield ['2015-01-02', true];
        yield ['2015-01-aa', false];
        yield ['2015-aa-02', false];
        yield ['aaaa-01-02', false];
        yield ['98-01-02', false];
        yield ['98-aa-02', false];
    }
}
