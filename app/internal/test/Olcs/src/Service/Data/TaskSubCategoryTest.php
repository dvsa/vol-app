<?php

namespace OlcsTest\Service\Data;

use CommonTest\Common\Service\Data\AbstractListDataServiceTestCase;
use Olcs\Service\Data\TaskSubCategory;

/**
 * @covers \Olcs\Service\Data\TaskSubCategory
 */
class TaskSubCategoryTest extends AbstractListDataServiceTestCase
{
    /** @var TaskSubCategory */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new TaskSubCategory($this->abstractListDataServiceServices);
    }

    public function testFetchListData()
    {
        static::assertEquals(TaskSubCategory::TYPE_IS_TASK, $this->sut->getCategoryType());
    }
}
