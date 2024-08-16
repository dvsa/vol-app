<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\TwoWeeksBefore;

/**
 * Two Weeks Before test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TwoWeeksBeforeTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery()
    {
        $bookmark = new TwoWeeksBefore();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRenderWithNoExpiryDate()
    {
        $bookmark = new TwoWeeksBefore();
        $bookmark->setData(
            [
                'expiryDate' => null
            ]
        );

        $this->assertEquals(
            '',
            $bookmark->render()
        );
    }

    public function testRenderWithTwoWeeksBefore()
    {
        $bookmark = new TwoWeeksBefore();
        $bookmark->setData(
            [
                'expiryDate' => '2014-01-16'
            ]
        );

        $this->assertEquals(
            '02/01/2014',
            $bookmark->render()
        );
    }
}
