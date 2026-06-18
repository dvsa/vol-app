<?php

namespace Dvsa\OlcsTest\Transfer\Query\BusRegSearchView;

use Dvsa\Olcs\Transfer\Query\BusRegSearchView\BusRegSearchViewList;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * @covers \Dvsa\Olcs\Transfer\Query\BusRegSearchView\BusRegSearchViewList
 */
class BusRegSearchViewListTest extends MockeryTestCase
{
    public function testGetSet()
    {
        $sut = BusRegSearchViewList::create(
            [
                'organisationId' => 9999,
                'licId' => 8888,
                'busRegStatus' => 'unit_BusRegStatus',
            ]
        );

        static::assertEquals(9999, $sut->getOrganisationId());
        static::assertEquals(8888, $sut->getLicId());
        static::assertEquals('unit_BusRegStatus', $sut->getBusRegStatus());
    }
}
