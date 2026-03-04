<?php

namespace Dvsa\OlcsTest\Api\Domain\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\DateTmRemoved;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class DateTmRemovedTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery()
    {
        $bookmark = new DateTmRemoved();

        $query = $bookmark->getQuery(['transportManagerLicence' => 123]);

        $this->assertInstanceOf(QueryInterface::class, $query);
    }

    public function testRenderWhenDateIsString()
    {
        $bookmark = new DateTmRemoved();
        $bookmark->setData([
            'deletedDate' => '2026-03-04 10:11:12',
        ]);

        $this->assertEquals('04/03/2026', $bookmark->render());
    }

    public function testRenderWhenDateIsDateTime()
    {
        $bookmark = new DateTmRemoved();
        $bookmark->setData([
            'deletedDate' => new \DateTime('2026-03-04 10:11:12'),
        ]);

        $this->assertEquals('04/03/2026', $bookmark->render());
    }

    public function testRenderWhenDateIsNull()
    {
        $bookmark = new DateTmRemoved();

        $bookmark->setData([
            'deletedDate' => null
        ]);

        $this->assertNull($bookmark->render());
    }

    public function testRenderWhenDateMissing()
    {
        $bookmark = new DateTmRemoved();

        $bookmark->setData([]);

        $this->assertNull($bookmark->render());
    }
}