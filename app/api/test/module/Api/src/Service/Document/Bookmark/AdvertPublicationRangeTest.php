<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\AdvertPublicationRange;
use PHPUnit\Framework\TestCase;

/**
 * AdvertPublicationRange test
 */
class AdvertPublicationRangeTest extends TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new AdvertPublicationRange();
        $query = $bookmark->getQuery(['application' => 456]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRenderWithValidDate(): void
    {
        $bookmark = new AdvertPublicationRange();
        $bookmark->setData([
            'receivedDate' => '2024-06-15'
        ]);

        // 2024-06-15 - 21 days = 2024-05-25
        // 2024-06-15 + 21 days = 2024-07-06
        $this->assertEquals('25/05/2024 - 06/07/2024', $bookmark->render());
    }

    public function testRenderWithNullDate(): void
    {
        $bookmark = new AdvertPublicationRange();
        $bookmark->setData([
            'receivedDate' => null
        ]);

        $this->assertEquals('', $bookmark->render());
    }

    public function testRenderWithEmptyDate(): void
    {
        $bookmark = new AdvertPublicationRange();
        $bookmark->setData([
            'receivedDate' => ''
        ]);

        $this->assertEquals('', $bookmark->render());
    }

    public function testRenderDateFormatting(): void
    {
        $bookmark = new AdvertPublicationRange();
        $bookmark->setData([
            'receivedDate' => '2024-01-05'
        ]);

        // Should have leading zeros in day and month
        // 2024-01-05 - 21 days = 2023-12-15
        // 2024-01-05 + 21 days = 2024-01-26
        $this->assertEquals('15/12/2023 - 26/01/2024', $bookmark->render());
    }

    public function testRenderYearBoundary(): void
    {
        $bookmark = new AdvertPublicationRange();
        $bookmark->setData([
            'receivedDate' => '2024-01-10'
        ]);

        // 2024-01-10 - 21 days = 2023-12-20
        // 2024-01-10 + 21 days = 2024-01-31
        $this->assertEquals('20/12/2023 - 31/01/2024', $bookmark->render());
    }

    public function testRenderLeapYear(): void
    {
        $bookmark = new AdvertPublicationRange();
        $bookmark->setData([
            'receivedDate' => '2024-03-10'
        ]);

        // 2024 is a leap year
        // 2024-03-10 - 21 days = 2024-02-18
        // 2024-03-10 + 21 days = 2024-03-31
        $this->assertEquals('18/02/2024 - 31/03/2024', $bookmark->render());
    }

    public function testRenderMissingReceivedDate(): void
    {
        $bookmark = new AdvertPublicationRange();
        $bookmark->setData([]);

        $this->assertEquals('', $bookmark->render());
    }
}
