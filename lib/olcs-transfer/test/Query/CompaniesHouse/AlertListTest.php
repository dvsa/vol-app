<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\CompaniesHouse;

use Dvsa\Olcs\Transfer\Query\CompaniesHouse\AlertList;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @see AlertList
 */
final class AlertListTest extends MockeryTestCase
{
    public function testStructure()
    {
        $trafficAreas = ['A','B'];
        $includeClosed = false;
        $typeOfChange = ['company_status_change'];

        $sut = AlertList::create(
            [
                'trafficAreas' => $trafficAreas,
                'includeClosed' => $includeClosed,
                'typeOfChange' => $typeOfChange,
            ]
        );

        $this->assertEquals($trafficAreas, $sut->getTrafficAreas());
        $this->assertFalse($sut->getIncludeClosed());
        $this->assertEquals($typeOfChange, $sut->getTypeOfChange());
    }
}
