<?php

/**
 * Date Helper Service Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Service\Helper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Service\Helper\DateHelperService;

/**
 * Date Helper Service Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
final class DateHelperServiceTest extends MockeryTestCase
{
    public $sut;
    #[\Override]
    protected function setUp(): void
    {
        $this->sut = new DateHelperService();
    }

    public function testGetDateWithNoParams(): void
    {
        // as much as I don't like computed expectations in tests,
        // there's no real way round it here...
        $this->assertEquals(date('Y-m-d'), $this->sut->getDate());
    }

    public function testGetDateWithParams(): void
    {
        // as much as I don't like computed expectations in tests,
        // there's no real way round it here...
        $this->assertEquals(date('m-d'), $this->sut->getDate('m-d'));
    }

    public function testGetDateObject(): void
    {
        $this->assertInstanceOf('DateTime', $this->sut->getDateObject());
    }

    public function testGetDateObjectFromArray(): void
    {
        $obj = $this->sut->getDateObjectFromArray(
            [
                'day' => '07',
                'month' => '01',
                'year' => '2015'
            ]
        );

        $this->assertInstanceOf('DateTime', $obj);
        $this->assertSame('2015-01-07', $obj->format('Y-m-d'));
    }
}
