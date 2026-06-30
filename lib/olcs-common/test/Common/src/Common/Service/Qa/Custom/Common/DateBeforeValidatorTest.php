<?php

namespace CommonTest\Service\Qa\Custom\Common;

use Common\Service\Qa\Custom\Common\DateBeforeValidator;
use Common\Service\Qa\DateTimeFactory;
use DateTime;
use IntlDateFormatter;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\I18n\View\Helper\DateFormat;

/**
 * DateBeforeValidatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class DateBeforeValidatorTest extends MockeryTestCase
{
    public const DATE_MUST_BE_BEFORE_DATE_STRING = '2020-01-03';

    private $dateFormat;

    private $dateTimeFactory;

    private $dateBeforeValidator;

    #[\Override]
    protected function setUp(): void
    {
        $options = [
            'dateMustBeBefore' => self::DATE_MUST_BE_BEFORE_DATE_STRING
        ];

        $this->dateFormat = m::mock(DateFormat::class);

        $this->dateTimeFactory = m::mock(DateTimeFactory::class);

        $this->dateBeforeValidator = new DateBeforeValidator(
            $this->dateFormat,
            $this->dateTimeFactory,
            $options
        );
    }

    /**
     * @dataProvider dpIsValidTrue
     */
    public function testIsValidTrue($date): void
    {
        $this->assertTrue(
            $this->dateBeforeValidator->isValid($date)
        );
    }

    /**
     * @return string[][]
     *
     * @psalm-return list{list{'2020-01-02'}, list{'2020-01-01'}, list{'2019-12-31'}, list{'2019-12-30'}}
     */
    public function dpIsValidTrue(): array
    {
        return [
            ['2020-01-02'],
            ['2020-01-01'],
            ['2019-12-31'],
            ['2019-12-30'],
        ];
    }

    /**
     * @dataProvider dpIsValidFalse
     */
    public function testIsValidFalse($date): void
    {
        $dateMustBeBeforeDateTime = m::mock(DateTime::class);

        $formattedDateMustBeBefore = '3 Jan 2020';

        $this->dateTimeFactory->shouldReceive('create')
            ->with(self::DATE_MUST_BE_BEFORE_DATE_STRING)
            ->once()
            ->andReturn($dateMustBeBeforeDateTime);

        $this->dateFormat->shouldReceive('__invoke')
            ->with($dateMustBeBeforeDateTime, IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE)
            ->once()
            ->andReturn($formattedDateMustBeBefore);

        $this->assertFalse(
            $this->dateBeforeValidator->isValid($date)
        );

        $expectedMessages = [
            DateBeforeValidator::ERR_DATE_NOT_BEFORE => 'Date is too far away'
        ];

        $this->assertEquals(
            $expectedMessages,
            $this->dateBeforeValidator->getMessages()
        );

        $this->assertEquals(
            $formattedDateMustBeBefore,
            $this->dateBeforeValidator->__get('dateMustBeBefore')
        );
    }

    /**
     * @return string[][]
     *
     * @psalm-return list{list{'2020-01-03'}, list{'2020-01-04'}, list{'2020-01-05'}, list{'2020-02-01'}, list{'2021-03-28'}, list{'2022-01-01'}}
     */
    public function dpIsValidFalse(): array
    {
        return [
            ['2020-01-03'],
            ['2020-01-04'],
            ['2020-01-05'],
            ['2020-02-01'],
            ['2021-03-28'],
            ['2022-01-01'],
        ];
    }
}
