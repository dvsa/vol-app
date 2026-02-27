<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Query\Bus;

use Dvsa\Olcs\Api\Domain\Query\Bus\ByLicenceRoute;

/**
 * ByLicenceRoute test
 */
class ByLicenceRouteTest extends \PHPUnit\Framework\TestCase
{
    public function testStructure(): void
    {
        $licence = 1;
        $routeNo = 2;
        $busStatus = ['status', 'status2'];

        $query = ByLicenceRoute::create(
            [
                'licenceId' => $licence,
                'routeNo' => $routeNo,
                'busRegStatus' => $busStatus
            ]
        );

        $this->assertSame($routeNo, $query->getRouteNo());
        $this->assertSame($licence, $query->getLicenceId());
        $this->assertSame($busStatus, $query->getBusRegStatus());
    }
}
