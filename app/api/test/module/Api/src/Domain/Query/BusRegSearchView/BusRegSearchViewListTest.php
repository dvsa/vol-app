<?php

namespace Dvsa\OlcsTest\Api\Domain\Query\BusRegSearchView;

use Dvsa\Olcs\Api\Domain\Query\BusRegSearchView\BusRegSearchViewList;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

/**
 * @covers \Dvsa\Olcs\Api\Domain\Query\BusRegSearchView\BusRegSearchViewList
 */
class BusRegSearchViewListTest extends MockeryTestCase
{
    public function testGetSet()
    {
        $sut = BusRegSearchViewList::create(
            [
                'localAuthorityId' => 7777,
            ]
        );

        static::assertEquals(7777, $sut->getLocalAuthorityId());

        $sut->setLocalAuthorityId(7779);
        static::assertEquals(7779, $sut->getLocalAuthorityId());
    }
}
