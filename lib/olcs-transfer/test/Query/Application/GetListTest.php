<?php

namespace Dvsa\OlcsTest\Transfer\Query\Application;

use Dvsa\Olcs\Transfer\Query\Application\GetList;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Dvsa\Olcs\Transfer\Query\Application\GetList
 */
class GetListTest extends MockeryTestCase
{
    public function testGetSet()
    {
        $sut = GetList::create(
            [
                'organisation' => 'unit_Org',
                'status' => 'unit_Status',
            ]
        );

        static::assertEquals('unit_Org', $sut->getOrganisation());
        static::assertEquals('unit_Status', $sut->getStatus());
    }
}
