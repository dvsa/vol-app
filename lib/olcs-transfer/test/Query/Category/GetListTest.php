<?php

namespace Dvsa\OlcsTest\Transfer\Query\Category;

use Dvsa\Olcs\Transfer\Query\Category\GetList;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Dvsa\Olcs\Transfer\Query\Category\GetList
 */
class GetListTest extends MockeryTestCase
{
    public function testGetSet()
    {
        $sut = GetList::create(
            [
                'isTaskCategory' => 'unit_Task',
                'isDocCategory' => 'unit_Doc',
                'isScanCategory' => 'unit_Scan',
                'isOnlyWithItems' => 'unit_isOnlyWithItems',
            ]
        );

        static::assertEquals('unit_Task', $sut->getIsTaskCategory());
        static::assertEquals('unit_Doc', $sut->getIsDocCategory());
        static::assertEquals('unit_Scan', $sut->getIsScanCategory());
        static::assertEquals('unit_isOnlyWithItems', $sut->getIsOnlyWithItems());
    }
}
