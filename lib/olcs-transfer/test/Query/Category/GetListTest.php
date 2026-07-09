<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Query\Category;

use Dvsa\Olcs\Transfer\Query\Category\GetList;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Transfer\Query\Category\GetList::class)]
final class GetListTest extends MockeryTestCase
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

        $this->assertEquals('unit_Task', $sut->getIsTaskCategory());
        $this->assertEquals('unit_Doc', $sut->getIsDocCategory());
        $this->assertEquals('unit_Scan', $sut->getIsScanCategory());
        $this->assertEquals('unit_isOnlyWithItems', $sut->getIsOnlyWithItems());
    }
}
