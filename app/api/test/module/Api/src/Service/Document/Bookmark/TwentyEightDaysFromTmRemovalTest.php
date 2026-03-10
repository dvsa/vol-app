<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\TwentyEightDaysFromTmRemoval;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class TwentyEightDaysFromTmRemovalTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new TwentyEightDaysFromTmRemoval();

        $query = $bookmark->getQuery(['transportManagerLicence' => 123]);

        $this->assertInstanceOf(QueryInterface::class, $query);
    }

    public function testRenderReturnsNullWhenDeletedDateMissing(): void
    {
        $bookmark = new TwentyEightDaysFromTmRemoval();
        $bookmark->setData([]);

        $this->assertNull($bookmark->render());
    }

    public function testRenderReturnsNullWhenDeletedDateEmptyString(): void
    {
        $bookmark = new TwentyEightDaysFromTmRemoval();
        $bookmark->setData(['deletedDate' => '']);

        $this->assertNull($bookmark->render());
    }

    public function testRenderWhenDeletedDateIsStringAdds28Days(): void
    {
        $bookmark = new TwentyEightDaysFromTmRemoval();
        $bookmark->setData([
            'deletedDate' => '2026-03-04', // +28 days => 01/04/2026
        ]);

        $this->assertEquals('01/04/2026', $bookmark->render());
    }

    public function testRenderWhenDeletedDateIsDateTimeAdds28Days(): void
    {
        $bookmark = new TwentyEightDaysFromTmRemoval();
        $bookmark->setData([
            'deletedDate' => new \DateTime('2026-03-04'), // +28 days => 01/04/2026
        ]);

        $this->assertEquals('01/04/2026', $bookmark->render());
    }
}