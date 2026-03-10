<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\TwentyoneDaysFrom28DayDeadlineTM;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class TwentyoneDaysFrom28DayDeadlineTMTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new TwentyoneDaysFrom28DayDeadlineTM();

        $query = $bookmark->getQuery(['transportManagerLicence' => 123]);

        $this->assertInstanceOf(QueryInterface::class, $query);
    }

    public function testRenderReturnsNullWhenDeletedDateMissing(): void
    {
        $bookmark = new TwentyoneDaysFrom28DayDeadlineTM();
        $bookmark->setData([]);

        $this->assertNull($bookmark->render());
    }

    public function testRenderReturnsNullWhenDeletedDateNull(): void
    {
        $bookmark = new TwentyoneDaysFrom28DayDeadlineTM();
        $bookmark->setData(['deletedDate' => null]);

        $this->assertNull($bookmark->render());
    }

    public function testRenderReturnsNullWhenDeletedDateEmptyString(): void
    {
        $bookmark = new TwentyoneDaysFrom28DayDeadlineTM();
        $bookmark->setData(['deletedDate' => '']);

        $this->assertNull($bookmark->render());
    }

    public function testRenderWhenDeletedDateIsStringAdds49Days(): void
    {
        // 2026-03-04 + 49 days = 2026-04-22
        $bookmark = new TwentyoneDaysFrom28DayDeadlineTM();
        $bookmark->setData([
            'deletedDate' => '2026-03-04',
        ]);

        $this->assertEquals('22/04/2026', $bookmark->render());
    }

    public function testRenderWhenDeletedDateIsDateTimeAdds49Days(): void
    {
        // 2026-03-04 + 49 days = 2026-04-22
        $bookmark = new TwentyoneDaysFrom28DayDeadlineTM();
        $bookmark->setData([
            'deletedDate' => new \DateTime('2026-03-04'),
        ]);

        $this->assertEquals('22/04/2026', $bookmark->render());
    }
}