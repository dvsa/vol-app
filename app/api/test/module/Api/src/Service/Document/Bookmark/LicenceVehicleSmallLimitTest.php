<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\LicenceVehicleSmallLimit;

/**
 * Licence vehicle small limit test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceVehicleSmallLimitTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery()
    {
        $bookmark = new LicenceVehicleSmallLimit();
        $query = $bookmark->getQuery(['licence' => 123]);
        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRenderEmpty()
    {
        $bookmark = new LicenceVehicleSmallLimit();
        $bookmark->setData(['totAuthSmallVehicles' => null]);
        $this->assertEquals(LicenceVehicleSmallLimit::EMPTY_AUTH, $bookmark->render());
    }

    public function testRender()
    {
        $bookmark = new LicenceVehicleSmallLimit();
        $bookmark->setData(['totAuthSmallVehicles' => 1]);
        $this->assertEquals(1, $bookmark->render());
    }
}
