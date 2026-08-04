<?php

/**
 * Date Not In Past validator test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

declare(strict_types=1);

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
final class DateNotInPastValidatorTest extends MockeryTestCase
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

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsValidTrue')]
    public function testIsValidTrue($date): void
    {
        $this->assertTrue(
            $this->dateNotInPastValidator->isValid($date)
        );
    }

    /**
     * @return \Iterator<(int | string), array<string>>
     *
     * @psalm-return list{list{'2019-11-27'}, list{'2019-11-28'}, list{'2019-12-01'}, list{'2020-11-25'}}
     */
    public static function dpIsValidTrue(): \Iterator
    {
        yield ['2019-11-27'];
        yield ['2019-11-28'];
        yield ['2019-12-01'];
        yield ['2020-11-25'];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpIsValidFalse')]
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
     * @return \Iterator<(int | string), array<string>>
     *
     * @psalm-return list{list{'2019-11-26'}, list{'2019-11-01'}, list{'2019-10-30'}, list{'2018-06-06'}, list{'2018-12-12'}}
     */
    public static function dpIsValidFalse(): \Iterator
    {
        yield ['2019-11-26'];
        yield ['2019-11-01'];
        yield ['2019-10-30'];
        yield ['2018-06-06'];
        yield ['2018-12-12'];
    }
}
