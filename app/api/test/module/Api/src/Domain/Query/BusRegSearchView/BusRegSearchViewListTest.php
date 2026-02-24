<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Query\BusRegSearchView;

use Dvsa\Olcs\Api\Domain\Query\BusRegSearchView\BusRegSearchViewList;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Query\BusRegSearchView\BusRegSearchViewList::class)]
class BusRegSearchViewListTest extends MockeryTestCase
{
    public function testGetSet(): void
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
