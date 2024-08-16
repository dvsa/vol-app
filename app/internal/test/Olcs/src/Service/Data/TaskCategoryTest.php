<?php

namespace OlcsTest\Service\Data;

use CommonTest\Common\Service\Data\AbstractListDataServiceTestCase;
use Olcs\Service\Data\TaskCategory;

/**
 * @covers \Olcs\Service\Data\TaskCategory
 */
class TaskCategoryTest extends AbstractListDataServiceTestCase
{
    /** @var TaskCategory */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new TaskCategory($this->abstractListDataServiceServices);
    }

    public function testFetchListData()
    {
        static::assertEquals(TaskCategory::TYPE_IS_TASK, $this->sut->getCategoryType());
    }
}
