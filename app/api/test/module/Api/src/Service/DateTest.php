<?php

declare(strict_types=1);

/**
 * Date Helper Service Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Service;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Service\Date;
use Olcs\Logging\Log\Logger;

/**
 * Date Helper Service Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class DateTest extends MockeryTestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new Date();

        $logger = new \Dvsa\OlcsTest\SafeLogger();
        $logger->addWriter(new \Laminas\Log\Writer\Mock());
        Logger::setLogger($logger);
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
        $this->assertEquals('2015-01-07', $obj->format('Y-m-d'));
    }

    public function testCalculateDate(): void
    {
        $date = new \DateTime('2015-01-01');

        $result = $this->sut->calculateDate($date, 10);

        $this->assertEquals('2015-01-11', $result->format('Y-m-d'));
    }
}
