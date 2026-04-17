<?php

declare(strict_types=1);

/**
 * Add Months Rounding Down Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Util;

use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Domain\Util\SlaCalculator;
use Dvsa\Olcs\Api\Entity\System\Sla;
use Dvsa\Olcs\Api\Domain\Util\TimeProcessorBuilder;
use Dvsa\Olcs\Api\Domain\Repository\PublicHoliday as PublicHolidayRepo;
use Olcs\Logging\Log\Logger;

/**
 * SlaCalculator Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class SlaCalculatorTest extends MockeryTestCase
{
    public $publicHolidayRepo;
    public $dateTimeProcessor;
    public function setUp(): void
    {
        $logger = new \Dvsa\OlcsTest\SafeLogger();
        $logger->addWriter(new \Laminas\Log\Writer\Mock());
        Logger::setLogger($logger);

        $this->publicHolidayRepo = m::mock(PublicHolidayRepo::class);

        $this->dateTimeProcessor = new TimeProcessorBuilder($this->publicHolidayRepo);

        parent::setUp();
    }

    /**
     * Test the SLA date calculator given a set of params
     *
     *
     * @param $date String date to start from
     * @param $days Integer offset days from $date
     * @param $weekends Boolean if true, SLA should take weekends into account
     * @param $publicHolidays Boolean if true, public holidays are taken into account
     * @param $expected String expected result
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('slaDateProvider')]
    public function testApplySla(
        mixed $date,
        mixed $days,
        mixed $weekends,
        mixed $publicHolidays,
        mixed $trafficArea,
        mixed $expected
    ): void {
        $date = new \DateTime($date);

        $this->publicHolidayRepo->shouldReceive('fetchBetweenByTa')
            ->andReturnUsing(
                fn($startDate, $endDate, $trafficArea) => $this->generateSelectPublicHolidaysArray($startDate, $endDate)
            );

        $ta = $this->generateTa($trafficArea);
        $sla = $this->generateSla($days, $weekends, $publicHolidays);

        $slaCalculator = new SlaCalculator($this->dateTimeProcessor);

        $result = $slaCalculator->applySla($date, $sla, $ta);

        $this->assertEquals($expected, $result->format('Y-m-d'));
    }

    private function generateTa(mixed $trafficArea): mixed
    {
        $ta = m::mock(TrafficArea::class)->makePartial();
        $ta->setId($trafficArea);

        return $ta;
    }

    private function generateSla(mixed $days, mixed $weekends, mixed $publicHolidays): mixed
    {
        $sla = new Sla();
        $sla->setDays($days);
        $sla->setWeekend($weekends);
        $sla->setPublicHoliday($publicHolidays);

        return $sla;
    }

    public static function slaDateProvider(): array
    {
        return [
            ['2015-12-18', -35, true, false, 'B', '2015-10-30'],
            ['2015-12-18', -35, false, true, 'B', '2015-11-12'],
            ['2015-12-18', -35, false, false, 'B', '2015-11-13'],
            ['2015-12-18', -35, true, true, 'B', '2015-10-29'],
            ['2015-12-18', -14, true, true, 'B', '2015-11-27'],
            ['2015-12-18', 5, true, true, 'B', '2015-12-29'],
            ['2015-12-18', 20, true, true, 'B', '2016-01-20'],
            ['2015-12-18', 5, true, true, 'B', '2015-12-29'],
            ['2015-12-12', 5, true, true, 'B', '2015-12-18'],
            ['2015-12-18', 2, true, true, 'B', '2015-12-22'],
            ['2015-12-12', 60, true, true, 'B', '2016-03-09'],
            ['2015-01-01', 100, true, true, 'B', '2015-05-28'],
            ['2015-01-01', 100, true, false, 'B', '2015-05-21'],
            ['2015-01-01', 100, false, false, 'B', '2015-04-11'],
            ['2015-12-31', -100, true, true, 'B', '2015-08-07'],
            ['2015-12-31', -100, true, false, 'B', '2015-08-13'],
            ['2015-12-31', -100, false, false, 'B', '2015-09-22']
        ];
    }

    /**
     * Mimics the returned holiday dates between a start and end date
     *
     * @param $startDate
     * @param $endDate
     * @param $trafficArea
     * @return array
     */
    private function generateSelectPublicHolidaysArray(mixed $startDate, mixed $endDate): mixed
    {
        $publicHolidays = $this->getAllPublicHolidays();
        $returnHolidays = [];
        $startDate = $startDate->format('Y-m-d');
        $endDate = $endDate->format('Y-m-d');
        foreach ($publicHolidays as $ph) {
            if (($ph['publicHolidayDate'] >= $startDate) && ($ph['publicHolidayDate'] <= $endDate)) {
                array_push($returnHolidays, $ph);
            }
        }
        return $returnHolidays;
    }

    /**
     * Static list of all holidays as a data source
     *
     * @return array
     */
    private function getAllPublicHolidays(): array
    {
        return [
            // all public holidays for England 2015-16
            ['publicHolidayDate' => '2015-01-01'],
            ['publicHolidayDate' => '2015-04-03'],
            ['publicHolidayDate' => '2015-04-06'],
            ['publicHolidayDate' => '2015-05-04'],
            ['publicHolidayDate' => '2015-05-25'],
            ['publicHolidayDate' => '2015-08-31'],
            ['publicHolidayDate' => '2015-12-10'],
            ['publicHolidayDate' => '2015-12-25'],
            ['publicHolidayDate' => '2015-12-28'],
            ['publicHolidayDate' => '2016-01-01'],
            ['publicHolidayDate' => '2016-03-28'],
            ['publicHolidayDate' => '2016-05-02'],
            ['publicHolidayDate' => '2016-08-29'],
            ['publicHolidayDate' => '2016-12-26'],
            ['publicHolidayDate' => '2016-12-27']
        ];
    }
}
