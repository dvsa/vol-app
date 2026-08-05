<?php

declare(strict_types=1);

namespace OlcsTest\Service\Data;

use CommonTest\Common\Service\Data\AbstractListDataServiceTestCase;
use Olcs\Service\Data\TaskCategory;

#[\PHPUnit\Framework\Attributes\CoversClass(\Olcs\Service\Data\TaskCategory::class)]
final class TaskCategoryTest extends AbstractListDataServiceTestCase
{
    /** @var TaskCategory */
    private $sut;

    protected function setUp(): void
    {
        parent::setUp();

        $this->sut = new TaskCategory($this->abstractListDataServiceServices);
    }

    public function testFetchListData(): void
    {
        $this->assertEquals(TaskCategory::TYPE_IS_TASK, $this->sut->getCategoryType());
    }
}
