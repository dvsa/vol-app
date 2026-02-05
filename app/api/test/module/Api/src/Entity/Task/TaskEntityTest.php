<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Task;

use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;
use Dvsa\Olcs\Api\Entity\Task\Task as Entity;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;

/**
 * Task Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
class TaskEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testConstruct(): void
    {
        $category = new Category();
        $subCategory = new SubCategory();
        $task = new Entity($category, $subCategory);
        $this->assertEquals($category, $task->getCategory());
        $this->assertEquals($subCategory, $task->getSubCategory());
    }
}
