<?php

declare(strict_types=1);

/**
 * PVehicleRegistrationMark Test
 */

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\PVehicleRegistrationMark;

/**
 * PVehicleRegistrationMark Test
 */
final class PVehicleRegistrationMarkTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new PVehicleRegistrationMark();
        $query = $bookmark->getQuery(['impounding' => 123]);
        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
        $this->assertNotInstanceOf(\Dvsa\Olcs\Api\Domain\Query\Bookmark\ImpoundingBundle::class, $bookmark->getQuery([]));
    }

    public function testRender(): void
    {
        $bookmark = new PVehicleRegistrationMark();
        $bookmark->setData(['vrm' => 'AB12 CDE']);

        $this->assertEquals('AB12 CDE', $bookmark->render());
    }
}
