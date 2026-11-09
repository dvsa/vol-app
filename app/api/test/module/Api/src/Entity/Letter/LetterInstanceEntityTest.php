<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Letter;

use Dvsa\Olcs\Api\Entity\Letter\LetterInstance as Entity;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceTodo;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueTodo;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueType;
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
    private function issueAt(int $id, int $displayOrder, array $todoVersions, ?LetterIssueType $type = null): LetterInstanceIssue
    {
        $issueVersion = new LetterIssueVersion();
        $issueVersion->setLetterIssueType($type);
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

    private function issueType(int $id): LetterIssueType
    {
        $type = new LetterIssueType();
        $type->setId($id);

        return $type;
    }

    /**
     * The letter draws issues grouped by type, so "first" means first as printed, not just the
     * lowest display order.
     */
    public function testLetterOrderGroupsIssuesByType(): void
    {
        $shared = $this->todoVersion(32);
        $x = $this->issueType(1);
        $y = $this->issueType(2);

        $x1 = $this->issueAt(10, 0, [], $x);
        $y1 = $this->issueAt(20, 1, [$shared], $y);
        $x2 = $this->issueAt(30, 2, [$shared], $x);
        $entity = new Entity();
        $entity->addLetterInstanceIssue($y1);
        $entity->addLetterInstanceIssue($x2);
        $entity->addLetterInstanceIssue($x1);
        $instanceTodo = $this->todoOwnedBy($y1, $shared);

        $this->assertSame([$x1, $x2, $y1], $entity->getIssuesInLetterOrder());
        $this->assertSame($x2, $entity->getPlacementIssue($instanceTodo));
    }

    public function testPlacementFollowsAReorderMadeAfterItWasFirstWorkedOut(): void
    {
        $shared = $this->todoVersion(32);
        $a = $this->issueAt(10, 0, [$shared]);
        $b = $this->issueAt(20, 1, [$shared]);
        $entity = new Entity();
        $entity->addLetterInstanceIssue($a);
        $entity->addLetterInstanceIssue($b);
        $instanceTodo = $this->todoOwnedBy($a, $shared);

        $this->assertSame($a, $entity->getPlacementIssue($instanceTodo));

        $a->setDisplayOrder(1);
        $b->setDisplayOrder(0);

        $this->assertSame($b, $entity->getPlacementIssue($instanceTodo));
    }

    public function testEachTodoListsTheIssuesItCanSitUnderById(): void
    {
        $shared = $this->todoVersion(32);
        $a = $this->issueAt(10, 0, [$shared]);
        $b = $this->issueAt(20, 1, [$shared]);
        $c = $this->issueAt(30, 2, []);
        $entity = new Entity();
        $entity->addLetterInstanceIssue($a);
        $entity->addLetterInstanceIssue($b);
        $entity->addLetterInstanceIssue($c);
        $sharedTodo = $this->todoOwnedBy($a, $shared);
        $sharedTodo->setId(1);
        $entity->addLetterInstanceTodo($sharedTodo);
        $stray = $this->todoOwnedBy($c, $this->todoVersion(99));
        $stray->setId(2);
        $entity->addLetterInstanceTodo($stray);

        $this->assertSame([1 => [10, 20], 2 => [30]], $entity->getTodoPlacementIssueIds());
        $this->assertSame([1 => [20, 10], 2 => [30]], $entity->getTodoPlacementIssueIds(Entity::SHARED_TODO_UNDER_LAST_ISSUE));
    }

    /**
     * The letter is drawn in one pass, so the placement of every to-do is worked out at once.
     */
    public function testEveryTodosPlacementIsWorkedOutInOnePass(): void
    {
        $shared = $this->todoVersion(32);
        $own = $this->todoVersion(33);
        $a = $this->issueAt(10, 0, [$shared]);
        $b = $this->issueAt(20, 1, [$shared, $own]);
        $entity = new Entity();
        $entity->addLetterInstanceIssue($a);
        $entity->addLetterInstanceIssue($b);
        $sharedTodo = $this->todoOwnedBy($a, $shared);
        $entity->addLetterInstanceTodo($sharedTodo);
        $ownTodo = $this->todoOwnedBy($b, $own);
        $entity->addLetterInstanceTodo($ownTodo);

        $placements = $entity->getPlacementIssuesByTodo();

        $this->assertSame($a, $placements[spl_object_id($sharedTodo)]);
        $this->assertSame($b, $placements[spl_object_id($ownTodo)]);
        $this->assertSame($b, $entity->getPlacementIssuesByTodo(Entity::SHARED_TODO_UNDER_LAST_ISSUE)[spl_object_id($sharedTodo)]);
    }
}
