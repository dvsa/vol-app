<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Letter;

use Dvsa\Olcs\Api\Entity\Letter\LetterInstance as Entity;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceTodo;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueTodo;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion;
use Dvsa\Olcs\Api\Entity\Letter\LetterTodo;
use Dvsa\Olcs\Api\Entity\Letter\LetterTodoVersion;
use Dvsa\OlcsTest\Api\Entity\Abstracts\EntityTester;

/**
 * LetterInstance Entity Unit Tests
 *
 * Initially auto-generated but won't be overridden
 */
final class LetterInstanceEntityTest extends EntityTester
{
    /**
     * Define the entity to test
     *
     * @var string
     */
    protected $entityClass = Entity::class;

    private function instanceIssueLinking(LetterTodoVersion $todoVersion): LetterInstanceIssue
    {
        $issueTodo = new LetterIssueTodo();
        $issueTodo->setLetterTodoVersion($todoVersion);

        $issueVersion = new LetterIssueVersion();
        $issueVersion->addLetterIssueTodo($issueTodo);

        $instanceIssue = new LetterInstanceIssue();
        $instanceIssue->setLetterIssueVersion($issueVersion);

        return $instanceIssue;
    }

    private function todoVersion(int $versionId, ?LetterTodo $todo = null): LetterTodoVersion
    {
        $version = new LetterTodoVersion();
        $version->setId($versionId);
        $version->setLetterTodo($todo);

        return $version;
    }

    /**
     * VOL-7408: issues can link different versions of the same to-do, which still counts as one to-do.
     */
    public function testTodoRequiringIssueCountsAreKeyedByTodoAcrossVersions(): void
    {
        $todo = new LetterTodo();
        $todo->setId(5);

        $entity = new Entity();
        $entity->addLetterInstanceIssue($this->instanceIssueLinking($this->todoVersion(32, $todo)));
        $entity->addLetterInstanceIssue($this->instanceIssueLinking($this->todoVersion(27, $todo)));

        $this->assertSame(['todo-5' => 2], $entity->getTodoRequiringIssueCounts());
    }

    public function testTodoRequiringIssueCountsFallBackToTheVersionWithoutAParent(): void
    {
        $entity = new Entity();
        $entity->addLetterInstanceIssue($this->instanceIssueLinking($this->todoVersion(27)));

        $this->assertSame(['version-27' => 1], $entity->getTodoRequiringIssueCounts());
    }

    /**
     * @param LetterTodoVersion[] $todoVersions
     */
    private function issueAt(int $id, int $displayOrder, array $todoVersions): LetterInstanceIssue
    {
        $issueVersion = new LetterIssueVersion();
        foreach ($todoVersions as $todoVersion) {
            $issueTodo = new LetterIssueTodo();
            $issueTodo->setLetterTodoVersion($todoVersion);
            $issueVersion->addLetterIssueTodo($issueTodo);
        }

        $instanceIssue = new LetterInstanceIssue();
        $instanceIssue->setId($id);
        $instanceIssue->setDisplayOrder($displayOrder);
        $instanceIssue->setLetterIssueVersion($issueVersion);

        return $instanceIssue;
    }

    private function todoOwnedBy(LetterInstanceIssue $owner, LetterTodoVersion $version): LetterInstanceTodo
    {
        $instanceTodo = new LetterInstanceTodo();
        $instanceTodo->setLetterTodoVersion($version);
        $owner->addLetterInstanceTodo($instanceTodo);

        return $instanceTodo;
    }

    /**
     * VOL-7737: a to-do shared by several issues sits under the first of them in letter order,
     * whatever order the issues were added in.
     */
    public function testSharedTodoIsPlacedUnderTheFirstIssueInLetterOrder(): void
    {
        $todo = new LetterTodo();
        $todo->setId(5);
        $shared = $this->todoVersion(32, $todo);

        $second = $this->issueAt(20, 1, [$shared]);
        $first = $this->issueAt(10, 0, [$shared]);
        $entity = new Entity();
        $entity->addLetterInstanceIssue($second);
        $entity->addLetterInstanceIssue($first);
        $instanceTodo = $this->todoOwnedBy($second, $shared);

        $this->assertSame($first, $entity->getPlacementIssue($instanceTodo));
        $this->assertSame(['todo-5' => [10, 20]], $entity->getTodoPlacementIssueIds());
    }

    public function testSharedTodoCanBePlacedUnderTheLastIssue(): void
    {
        $todo = new LetterTodo();
        $todo->setId(5);
        $shared = $this->todoVersion(32, $todo);

        $first = $this->issueAt(10, 0, [$shared]);
        $last = $this->issueAt(20, 1, [$shared]);
        $entity = new Entity();
        $entity->addLetterInstanceIssue($first);
        $entity->addLetterInstanceIssue($last);
        $instanceTodo = $this->todoOwnedBy($first, $shared);

        $this->assertSame($last, $entity->getPlacementIssue($instanceTodo, Entity::SHARED_TODO_UNDER_LAST_ISSUE));
        $this->assertSame(['todo-5' => [20, 10]], $entity->getTodoPlacementIssueIds(Entity::SHARED_TODO_UNDER_LAST_ISSUE));
    }

    public function testTodoNoIssueBringsStaysUnderItsOwnIssue(): void
    {
        $owner = $this->issueAt(10, 0, []);
        $entity = new Entity();
        $entity->addLetterInstanceIssue($owner);
        $instanceTodo = $this->todoOwnedBy($owner, $this->todoVersion(27));

        $this->assertSame($owner, $entity->getPlacementIssue($instanceTodo));
    }

    public function testSharedTodosDefaultToTheFirstIssue(): void
    {
        $this->assertSame(Entity::SHARED_TODO_UNDER_FIRST_ISSUE, Entity::SHARED_TODO_PLACEMENT);
    }
}
