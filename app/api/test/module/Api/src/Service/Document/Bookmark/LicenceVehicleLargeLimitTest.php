<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\LicenceVehicleLargeLimit;

/**
 * Licence vehicle large limit test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceVehicleLargeLimitTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery()
    {
        $bookmark = new LicenceVehicleLargeLimit();
        $query = $bookmark->getQuery(['licence' => 123]);
        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRenderEmpty()
    {
        $bookmark = new LicenceVehicleLargeLimit();
        $bookmark->setData(['totAuthLargeVehicles' => null, 'licenceType' => ['id' => 'ltyp_si']]);
        $this->assertEquals(LicenceVehicleLargeLimit::EMPTY_AUTH, $bookmark->render());
    }

    public function testRenderRestricted()
    {
        $bookmark = new LicenceVehicleLargeLimit();
        $bookmark->setData(['totAuthLargeVehicles' => null, 'licenceType' => ['id' => 'ltyp_r']]);
        $this->assertEquals(LicenceVehicleLargeLimit::NA, $bookmark->render());
    }

    public function testRender()
    {
        $bookmark = new LicenceVehicleLargeLimit();
        $bookmark->setData(['totAuthLargeVehicles' => 1, 'licenceType' => ['id' => 'ltyp_si']]);
        $this->assertEquals(1, $bookmark->render());
    }
}
