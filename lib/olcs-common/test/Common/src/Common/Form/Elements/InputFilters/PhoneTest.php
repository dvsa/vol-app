<?php

declare(strict_types=1);

namespace CommonTest\Form\Elements\InputFilters;

use Common\Form\Elements\InputFilters\Phone;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Common\Form\Elements\InputFilters\Phone::class)]
final class PhoneTest extends MockeryTestCase
{
    public function testInit(): void
    {
        $sut = new Phone();

        $sut->init();

        $this->assertSame('\d(\+|-|\(|\))*', $sut->getAttribute('pattern'));
        $this->assertSame('contact-number-optional', $sut->getLabel());
    }

    public function testValidators(): void
    {
        /** @var Phone $sut */
        $sut = m::mock(Phone::class)->makePartial()
            ->shouldReceive('getName')->once()->andReturn('unit_Name')
            ->getMock();

        $actual = $sut->getInputSpecification();

        $this->assertEquals('unit_Name', $actual['name']);
        $this->assertFalse($actual['required']);
        $this->assertSame([
            \Laminas\Validator\NotEmpty::class,
            \Laminas\Validator\Regex::class,
            \Laminas\Validator\StringLength::class,
        ], array_map(
            static fn($item) => $item['name'],
            $actual['validators']
        ));
    }
}
