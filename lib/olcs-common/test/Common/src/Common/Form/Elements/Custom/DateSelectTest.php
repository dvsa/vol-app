<?php

namespace CommonTest\Form\Elements\Custom;

use Common\Form\Elements\Custom\DateSelect;
use Laminas\Validator\Date as DateValidator;

/**
 * @covers \Common\Form\Elements\Custom\DateSelect
 */
class DateSelectTest extends \PHPUnit\Framework\TestCase
{
    /** @var  DateSelect */
    private $sut;

    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new DateSelect('foo');
    }

    public function testGetInputSpecification(): void
    {
        $this->sut->setOptions([]);

        $spec = $this->sut->getInputSpecification();

        $this->assertEquals('foo', $spec['name']);
        $this->assertEquals(null, $spec['required']);
        $this->assertCount(1, $spec['validators']);
        $this->assertCount(1, $spec['filters']);

        $validator = $spec['validators'][0];

        $this->assertInstanceOf(\Laminas\Validator\Date::class, $validator);
        $this->assertEquals('Y-m-d', $validator->getFormat());

        $validatorMessageTemplates = $validator->getMessageTemplates();
        $this->assertArrayHasKey(DateValidator::FALSEFORMAT, $validatorMessageTemplates);
        $this->assertEquals(
            $validatorMessageTemplates[DateValidator::FALSEFORMAT],
            "The input does not fit the date format 'DD MM YYYY'"
        );

        // Test the filter
        $this->assertNull($spec['filters'][0]['options']['callback']('foo'));
        $this->assertNull($spec['filters'][0]['options']['callback'](['year' => '2015']));
        $this->assertNull($spec['filters'][0]['options']['callback'](['year' => '2015', 'month' => '02']));
        $this->assertEquals(
            '2015-02-01',
            $spec['filters'][0]['options']['callback'](['year' => '2015', 'month' => '02', 'day' => '01'])
        );
        static::assertEquals('date-hint', $this->sut->getOption('hint'));
    }

    public function testSetOptionsMinAndMaxYear(): void
    {
        $options = [
            'max_year_delta' => '+5',
            'min_year_delta' => '-5'
        ];

        $year = date('Y');

        $this->sut->setOptions($options);

        $this->assertEquals(($year + 5), $this->sut->getMaxYear());
        $this->assertEquals(($year - 5), $this->sut->getMinYear());
    }

    public function testSetOptionsMaxYear(): void
    {
        $options = [
            'max_year_delta' => '+5'
        ];

        $year = date('Y');

        $this->sut->setOptions($options);

        $this->assertEquals(($year + 5), $this->sut->getMaxYear());
        $this->assertEquals($year, $this->sut->getMinYear());
    }

    public function testSetOptionsMinYear(): void
    {
        $options = [
            'min_year_delta' => '-5'
        ];

        $year = date('Y');

        $this->sut->setOptions($options);

        $this->assertEquals($year, $this->sut->getMaxYear());
        $this->assertEquals(($year - 5), $this->sut->getMinYear());
    }

    public function testSetOptionsDefaultDateNow(): void
    {
        $options = [
            'hint' => 'unit_Hint',
            'default_date' => 'now',
        ];

        $this->sut->setOptions($options);

        $this->assertEquals(date('Y-m-d'), $this->sut->getValue());
        static::assertEquals('unit_Hint', $this->sut->getOption('hint'));
    }

    public function testSetOptionsDefaultDate(): void
    {
        $options = [
            'default_date' => '+3 months'
        ];

        $this->sut->setOptions($options);

        $this->assertEquals(date('Y-m-d', strtotime('+3 months')), $this->sut->getValue());
    }
}
