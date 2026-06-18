<?php

/**
 * Date Not In Past validator test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace CommonTest\Service\Qa;

use Common\Service\Qa\DateNotInPastValidator;
use Common\Service\Qa\DateTimeFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Date Not In Past validator Test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class DateNotInPastValidatorTest extends MockeryTestCase
{
    private $dateNotInPastValidator;

    #[\Override]
    protected function setUp(): void
    {
        $options = [];

        $dateTimeFactory = m::mock(DateTimeFactory::class);
        $dateTimeFactory->shouldReceive('create->format')
            ->with('Y-m-d')
            ->andReturn('2019-11-27');

        $this->dateNotInPastValidator = new DateNotInPastValidator($dateTimeFactory, $options);
    }

    /**
     * @dataProvider dpIsValidTrue
     */
    public function testIsValidTrue($date): void
    {
        $this->assertTrue(
            $this->dateNotInPastValidator->isValid($date)
        );
    }

    /**
     * @return string[][]
     *
     * @psalm-return list{list{'2019-11-27'}, list{'2019-11-28'}, list{'2019-12-01'}, list{'2020-11-25'}}
     */
    public function dpIsValidTrue(): array
    {
        return [
            ['2019-11-27'],
            ['2019-11-28'],
            ['2019-12-01'],
            ['2020-11-25'],
        ];
    }

    /**
     * @dataProvider dpIsValidFalse
     */
    public function testIsValidFalse($date): void
    {
        $this->assertFalse(
            $this->dateNotInPastValidator->isValid($date)
        );

        $expectedMessages = [
            DateNotInPastValidator::ERR_DATE_IN_PAST => 'Date is in the past'
        ];

        $this->assertEquals(
            $expectedMessages,
            $this->dateNotInPastValidator->getMessages()
        );
    }

    /**
     * @return string[][]
     *
     * @psalm-return list{list{'2019-11-26'}, list{'2019-11-01'}, list{'2019-10-30'}, list{'2018-06-06'}, list{'2018-12-12'}}
     */
    public function dpIsValidFalse(): array
    {
        return [
            ['2019-11-26'],
            ['2019-11-01'],
            ['2019-10-30'],
            ['2018-06-06'],
            ['2018-12-12'],
        ];
    }
}
