<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\CompaniesHouse;

use Dvsa\Olcs\Transfer\Query\CompaniesHouse\AlertList;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @see AlertList
 */
class AlertListTest extends MockeryTestCase
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

        static::assertEquals($trafficAreas, $sut->getTrafficAreas());
        static::assertFalse($sut->getIncludeClosed());
        static::assertEquals($typeOfChange, $sut->getTypeOfChange());
    }
}
