<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Date1stTmLetterSent;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class Date1stTmLetterSentTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery()
    {
        $bookmark = new Date1stTmLetterSent();

        $query = $bookmark->getQuery(['transportManagerLicence' => 123]);

        $this->assertInstanceOf(QueryInterface::class, $query);
    }

    public function testRenderWhenDateIsString()
    {
        $bookmark = new Date1stTmLetterSent();
        $bookmark->setData([
            'lastTmFirstEmailDate' => '2026-03-04 10:11:12',
        ]);

        $this->assertEquals('04/03/2026', $bookmark->render());
    }

    public function testRenderWhenDateIsDateTime()
    {
        $bookmark = new Date1stTmLetterSent();
        $bookmark->setData([
            'lastTmFirstEmailDate' => new \DateTime('2026-03-04 10:11:12'),
        ]);

        $this->assertEquals('04/03/2026', $bookmark->render());
    }

    public function testRenderWhenDateIsNull()
    {
        $bookmark = new Date1stTmLetterSent();

        $bookmark->setData([
            'lastTmFirstEmailDate' => null
        ]);

        $this->assertNull($bookmark->render());
    }

    public function testRenderWhenDateMissing()
    {
        $bookmark = new Date1stTmLetterSent();

        $bookmark->setData([]);

        $this->assertNull($bookmark->render());
    }
}