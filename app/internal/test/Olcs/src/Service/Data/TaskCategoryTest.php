<?php

namespace OlcsTest\Service\Data;

use CommonTest\Service\Data\AbstractDataServiceTestCase;
use Olcs\Service\Data\TaskCategory;

/**
 * @covers \Olcs\Service\Data\TaskCategory
 */
class TaskCategoryTest extends AbstractDataServiceTestCase
{
    public function testFetchListData()
    {
        $sut = new TaskCategory();

        static::assertEquals(TaskCategory::TYPE_IS_TASK, $sut->getCategoryType());
    }
}
