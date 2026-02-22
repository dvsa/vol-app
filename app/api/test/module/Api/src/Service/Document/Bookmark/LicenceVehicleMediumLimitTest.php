<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\LicenceVehicleMediumLimit;

/**
 * Licence vehicle medium limit test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceVehicleMediumLimitTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new LicenceVehicleMediumLimit();
        $query = $bookmark->getQuery(['licence' => 123]);
        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRenderEmpty(): void
    {
        $bookmark = new LicenceVehicleMediumLimit();
        $bookmark->setData(['totAuthMediumVehicles' => null]);
        $this->assertEquals(LicenceVehicleMediumLimit::EMPTY_AUTH, $bookmark->render());
    }

    public function testRender(): void
    {
        $bookmark = new LicenceVehicleMediumLimit();
        $bookmark->setData(['totAuthMediumVehicles' => 1]);
        $this->assertEquals(1, $bookmark->render());
    }
}
