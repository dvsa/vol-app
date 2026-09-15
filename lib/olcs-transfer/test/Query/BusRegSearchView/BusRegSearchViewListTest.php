<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\BusRegSearchView;

use Dvsa\Olcs\Transfer\Query\BusRegSearchView\BusRegSearchViewList;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\BusRegSearchView\BusRegSearchViewList::class)]
final class BusRegSearchViewListTest extends MockeryTestCase
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

        $this->assertEquals(9999, $sut->getOrganisationId());
        $this->assertEquals(8888, $sut->getLicId());
        $this->assertEquals('unit_BusRegStatus', $sut->getBusRegStatus());
    }
}
