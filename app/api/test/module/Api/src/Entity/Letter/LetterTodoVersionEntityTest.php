<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Letter;

use Dvsa\Olcs\Api\Entity\Letter\LetterTodo;
use Dvsa\Olcs\Api\Entity\Letter\LetterTodoVersion as Entity;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;

/**
 * LetterTodoVersion Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
final class LetterTodoVersionEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    public function testDedupeKeyUsesTheParentTodo(): void
    {
        $todo = new LetterTodo();
        $todo->setId(5);
        $version = new Entity();
        $version->setId(27);
        $version->setLetterTodo($todo);

        $this->assertSame('todo-5', $version->getDedupeKey());
    }

    public function testDedupeKeyFallsBackToTheVersionWithoutAParent(): void
    {
        $version = new Entity();
        $version->setId(27);

        $this->assertSame('version-27', $version->getDedupeKey());
    }
}
