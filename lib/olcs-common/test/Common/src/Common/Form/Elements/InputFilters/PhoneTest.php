<?php

namespace CommonTest\Form\Elements\InputFilters;

use Common\Form\Elements\InputFilters\Phone;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * @covers \Common\Form\Elements\InputFilters\Phone
 */
class PhoneTest extends MockeryTestCase
{
    public function testInit(): void
    {
        $sut = new Phone();

        $sut->init();

        static::assertSame('\d(\+|-|\(|\))*', $sut->getAttribute('pattern'));
        static::assertSame('contact-number-optional', $sut->getLabel());
    }

    public function testValidators(): void
    {
        /** @var Phone $sut */
        $sut = m::mock(Phone::class)->makePartial()
            ->shouldReceive('getName')->once()->andReturn('unit_Name')
            ->getMock();

        $actual = $sut->getInputSpecification();

        static::assertEquals('unit_Name', $actual['name']);
        static::assertFalse($actual['required']);
        static::assertEquals(
            [
                \Laminas\Validator\NotEmpty::class,
                \Laminas\Validator\Regex::class,
                \Laminas\Validator\StringLength::class,
            ],
            array_map(
                static fn($item) => $item['name'],
                $actual['validators']
            )
        );
    }
}
