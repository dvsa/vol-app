<?php

namespace OlcsTest\Service\Data;

use CommonTest\Service\Data\AbstractDataServiceTestCase;
use Olcs\Service\Data\TaskSubCategory;

/**
 * @covers \Olcs\Service\Data\TaskSubCategory
 */
class TaskSubCategoryTest extends AbstractDataServiceTestCase
{
    public function testFetchListData()
    {
        $sut = new TaskSubCategory();

        static::assertEquals(TaskSubCategory::TYPE_IS_TASK, $sut->getCategoryType());
    }
}
