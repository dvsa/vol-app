<?php

declare(strict_types=1);

namespace OlcsTest\Service\Data;

use CommonTest\Common\Service\Data\AbstractListDataServiceTestCase;
use Olcs\Service\Data\TaskSubCategory;

#[\PHPUnit\Framework\Attributes\CoversClass(\Olcs\Service\Data\TaskSubCategory::class)]
class TaskSubCategoryTest extends AbstractListDataServiceTestCase
{
    /** @var TaskSubCategory */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new TaskSubCategory($this->abstractListDataServiceServices);
    }

    public function testFetchListData(): void
    {
        static::assertEquals(TaskSubCategory::TYPE_IS_TASK, $this->sut->getCategoryType());
    }
}
