<?php

namespace CommonTest\Form\Elements\InputFilters;

use Common\Form\Elements\InputFilters\PhoneRequired;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * @covers \Common\Form\Elements\InputFilters\PhoneRequired
 */
class PhoneRequiredTest extends MockeryTestCase
{
    public function testInit(): void
    {
        $sut = new PhoneRequired();

        $sut->init();

        static::assertSame('\d(\+|-|\(|\))*', $sut->getAttribute('pattern'));
        static::assertSame('contact-number', $sut->getLabel());
    }

    public function testValidators(): void
    {
        /** @var PhoneRequired $sut */
        $sut = m::mock(PhoneRequired::class)->makePartial()
            ->shouldReceive('getName')->once()->andReturn('unit_Name')
            ->getMock();

        $actual = $sut->getInputSpecification();

        static::assertEquals('unit_Name', $actual['name']);
        static::assertTrue($actual['required']);
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
