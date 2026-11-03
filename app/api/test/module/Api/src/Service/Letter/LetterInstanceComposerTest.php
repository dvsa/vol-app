<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Letter;

use Dvsa\Olcs\Api\Entity\Letter\LetterAppendixVersion;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceTodo;
use Dvsa\Olcs\Api\Service\Letter\IssueChange\IssueChangePlan;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueTodo;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion;
use Dvsa\Olcs\Api\Entity\Letter\LetterSection;
use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVariant;
use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion;
use Dvsa\Olcs\Api\Entity\Letter\LetterTodo;
use Dvsa\Olcs\Api\Entity\Letter\LetterTodoVersion;
use Dvsa\Olcs\Api\Service\Letter\LetterInstanceComposer;
use Dvsa\Olcs\Api\Service\Letter\Resolution\ResolvedSection;
use Dvsa\Olcs\Api\Service\Letter\Resolution\SectionResolution;
use Dvsa\Olcs\Api\Service\Letter\Resolution\UnresolvedSection;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * LetterInstanceComposer
 *
 * Shared by real letter generation and the letter type builder's preview, so that the two cannot
 * disagree about what a letter contains.
 */
class LetterInstanceComposerTest extends TestCase
{
    private LetterInstanceComposer $sut;

    protected function setUp(): void
    {
        $this->sut = new LetterInstanceComposer();
    }

    protected function tearDown(): void
    {
        m::close();
    }

    private function resolvedSection(int $displayOrder): ResolvedSection
    {
        return new ResolvedSection(
            new LetterSection(),
            new LetterSectionVariant(),
            new LetterSectionVersion(),
            $displayOrder,
            false
        );
    }

    private function todoVersion(int $id): LetterTodoVersion
    {
        $todoVersion = m::mock(LetterTodoVersion::class)->makePartial();
        $todoVersion->setId($id);

        return $todoVersion;
    }

    private function todoVersionOf(LetterTodo $todo, int $versionId): LetterTodoVersion
    {
        $todoVersion = m::mock(LetterTodoVersion::class)->makePartial();
        $todoVersion->setId($versionId);
        $todoVersion->setLetterTodo($todo);

        return $todoVersion;
    }

    private function todoWithCurrentVersion(int $todoId, int $currentVersionId): LetterTodo
    {
        $todo = m::mock(LetterTodo::class)->makePartial();
        $todo->setId($todoId);
        $todo->setCurrentVersion($this->todoVersionOf($todo, $currentVersionId));

        return $todo;
    }

    /**
     * @param LetterTodoVersion[] $todoVersions
     */
    private function issueVersionWithTodos(array $todoVersions): LetterIssueVersion
    {
        $issueTodos = [];
        foreach ($todoVersions as $todoVersion) {
            $issueTodo = m::mock(LetterIssueTodo::class)->makePartial();
            $issueTodo->shouldReceive('getLetterTodoVersion')->andReturn($todoVersion);
            $issueTodos[] = $issueTodo;
        }

        $issueVersion = m::mock(LetterIssueVersion::class)->makePartial();
        $issueVersion->shouldReceive('getLetterIssueTodos')->andReturn($issueTodos);

        return $issueVersion;
    }

    public function testComposesSectionsInTheOrderResolved(): void
    {
        $letterInstance = new LetterInstance();

        $this->sut->composeSections($letterInstance, new SectionResolution([
            $this->resolvedSection(0),
            $this->resolvedSection(1),
            $this->resolvedSection(2),
        ], []));

        $sections = $letterInstance->getLetterInstanceSections();

        $this->assertCount(3, $sections);
        $orders = [];
        foreach ($sections as $section) {
            $orders[] = $section->getDisplayOrder();
            $this->assertSame($letterInstance, $section->getLetterInstance());
        }
        $this->assertSame([0, 1, 2], $orders);
    }

    public function testComposesNothingForAnEmptyResolution(): void
    {
        $letterInstance = new LetterInstance();

        $this->sut->composeSections($letterInstance, new SectionResolution([], []));

        $this->assertCount(0, $letterInstance->getLetterInstanceSections());
    }

    /**
     * Unresolved sections are deliberately not composed: they have no version to read content from
     * and would fatal the renderer. They are reported to the caller instead.
     */
    public function testUnresolvedSectionsAreNotComposed(): void
    {
        $letterInstance = new LetterInstance();

        $unresolved = new UnresolvedSection(
            new LetterSection(),
            1,
            true,
            UnresolvedSection::REASON_NO_MATCHING_VARIANT
        );

        $this->sut->composeSections($letterInstance, new SectionResolution(
            [$this->resolvedSection(0)],
            [$unresolved]
        ));

        $this->assertCount(1, $letterInstance->getLetterInstanceSections());
    }

    public function testComposesIssuesInOrder(): void
    {
        $letterInstance = new LetterInstance();

        $first = m::mock(LetterIssueVersion::class)->makePartial();
        $second = m::mock(LetterIssueVersion::class)->makePartial();

        $this->sut->composeIssues($letterInstance, [$first, $second]);

        $issues = $letterInstance->getLetterInstanceIssues();
        $this->assertCount(2, $issues);
        $this->assertSame($first, $issues->first()->getLetterIssueVersion());
        $this->assertSame([0, 1], array_map(
            static fn($i): int => $i->getDisplayOrder(),
            $issues->toArray()
        ));
    }

    /**
     * Two issues frequently link the same to-do. Without deduplication the operator is told to do
     * the same thing twice (VOL-7280).
     */
    public function testTheSameTodoReachedFromTwoIssuesIsOnlyAddedOnce(): void
    {
        $shared = $this->todoVersion(1);

        $letterInstance = new LetterInstance();
        $this->sut->composeIssues($letterInstance, [
            $this->issueVersionWithTodos([$shared]),
            $this->issueVersionWithTodos([$shared]),
        ]);

        $this->sut->composeTodos($letterInstance);

        $this->assertCount(1, $letterInstance->getLetterInstanceTodos());
    }

    /**
     * A deduplicated to-do hangs off the first issue that brought it, which is what gives the
     * renderer "appears under the first issue type it relates to" without the renderer knowing.
     */
    public function testADeduplicatedTodoAttachesToTheFirstIssueThatBroughtIt(): void
    {
        $shared = $this->todoVersion(1);

        $letterInstance = new LetterInstance();
        $this->sut->composeIssues($letterInstance, [
            $this->issueVersionWithTodos([$shared]),
            $this->issueVersionWithTodos([$shared]),
        ]);

        $this->sut->composeTodos($letterInstance);

        $firstIssue = $letterInstance->getLetterInstanceIssues()->first();
        $this->assertSame($firstIssue, $letterInstance->getLetterInstanceTodos()->first()->getLetterInstanceIssue());
    }

    public function testDistinctTodosAreAllKept(): void
    {
        $letterInstance = new LetterInstance();
        $this->sut->composeIssues($letterInstance, [
            $this->issueVersionWithTodos([$this->todoVersion(1), $this->todoVersion(2)]),
            $this->issueVersionWithTodos([$this->todoVersion(3)]),
        ]);

        $this->sut->composeTodos($letterInstance);

        $this->assertCount(3, $letterInstance->getLetterInstanceTodos());
    }

    /**
     * VOL-7408: one issue links the to-do's current version, another still links an older one.
     */
    public function testTwoVersionsOfTheSameTodoAreOnlyAddedOnce(): void
    {
        $todo = $this->todoWithCurrentVersion(5, 32);

        $letterInstance = new LetterInstance();
        $this->sut->composeIssues($letterInstance, [
            $this->issueVersionWithTodos([$todo->getCurrentVersion()]),
            $this->issueVersionWithTodos([$this->todoVersionOf($todo, 27)]),
        ]);

        $this->sut->composeTodos($letterInstance);

        $this->assertCount(1, $letterInstance->getLetterInstanceTodos());
    }

    public function testTheTodosCurrentVersionIsUsedEvenWhenTheIssueLinksAnOlderOne(): void
    {
        $todo = $this->todoWithCurrentVersion(5, 32);

        $letterInstance = new LetterInstance();
        $this->sut->composeIssues($letterInstance, [
            $this->issueVersionWithTodos([$this->todoVersionOf($todo, 27)]),
        ]);

        $this->sut->composeTodos($letterInstance);

        $this->assertSame(32, $letterInstance->getLetterInstanceTodos()->first()->getLetterTodoVersion()->getId());
    }

    /**
     * An instance issue at a given display order, already on the letter.
     *
     * @param LetterTodoVersion[] $todoVersions
     */
    private function instanceIssue(LetterInstance $letterInstance, int $displayOrder, array $todoVersions): LetterInstanceIssue
    {
        $instanceIssue = new LetterInstanceIssue();
        $instanceIssue->setLetterIssueVersion($this->issueVersionWithTodos($todoVersions));
        $instanceIssue->setDisplayOrder($displayOrder);
        $letterInstance->addLetterInstanceIssue($instanceIssue);

        return $instanceIssue;
    }

    /**
     * A to-do row that is already on the letter, owned by the given issue.
     */
    private function existingTodo(LetterInstance $letterInstance, LetterInstanceIssue $owner, LetterTodoVersion $todoVersion): LetterInstanceTodo
    {
        $instanceTodo = new LetterInstanceTodo();
        $instanceTodo->setLetterTodoVersion($todoVersion);
        $instanceTodo->setLetterInstanceIssue($owner);
        $owner->addLetterInstanceTodo($instanceTodo);
        $letterInstance->addLetterInstanceTodo($instanceTodo);

        return $instanceTodo;
    }

    /**
     * @return array<int|null>
     */
    private function todoVersionIds(LetterInstance $letterInstance): array
    {
        return array_map(
            static fn(LetterInstanceTodo $t): ?int => $t->getLetterTodoVersion()->getId(),
            $letterInstance->getLetterInstanceTodos()->toArray()
        );
    }

    public function testTodoDisplayOrderFollowsIssueOrder(): void
    {
        $letterInstance = new LetterInstance();
        $this->sut->composeIssues($letterInstance, [
            $this->issueVersionWithTodos([$this->todoVersion(1), $this->todoVersion(2)]),
            $this->issueVersionWithTodos([$this->todoVersion(3)]),
        ]);

        $this->sut->composeTodos($letterInstance);

        $orders = [];
        foreach ($letterInstance->getLetterInstanceTodos() as $todo) {
            $orders[$todo->getLetterTodoVersion()->getId()] = $todo->getDisplayOrder();
        }
        $this->assertSame([1 => 0, 2 => 1, 3 => 2], $orders);
    }

    public function testReconcileKeepsAnExistingTodoRowAndItsEdits(): void
    {
        $t1 = $this->todoVersion(1);
        $letterInstance = new LetterInstance();
        $issue = $this->instanceIssue($letterInstance, 0, [$t1]);
        $existing = $this->existingTodo($letterInstance, $issue, $t1);
        $existing->setEditedDescription(['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'edited']]]]);
        $existing->setIsRendered(0);

        $result = $this->sut->reconcileTodos($letterInstance);

        $this->assertSame([], $result['created']);
        $this->assertSame([], $result['moved']);
        $this->assertCount(1, $letterInstance->getLetterInstanceTodos());
        $this->assertSame($existing, $letterInstance->getLetterInstanceTodos()->first());
        $this->assertSame('edited', $existing->getEditedDescription()['blocks'][0]['data']['text']);
        $this->assertSame(0, $existing->getIsRendered());
    }

    /**
     * Doctrine deletes a row taken out of an orphanRemoval collection, whatever happens to it next,
     * so a to-do changing issue is carried over to a new row with everything the caseworker
     * sees: their edit, the grab snapshot and whether it is shown.
     */
    public function testReconcileCarriesASharedTodoOverToTheNextIssueThatBringsIt(): void
    {
        $t1 = $this->todoVersion(1);
        $letterInstance = new LetterInstance();
        $a = $this->instanceIssue($letterInstance, 0, [$t1]);
        $b = $this->instanceIssue($letterInstance, 1, [$t1]);
        $shared = $this->existingTodo($letterInstance, $a, $t1);
        $shared->setEditedDescription(['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'edited']]]]);
        $shared->setGeneratedDescription(['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'snapshot']]]]);
        $shared->setIsRendered(0);

        $letterInstance->getLetterInstanceIssues()->removeElement($a);
        $result = $this->sut->reconcileTodos($letterInstance);

        $this->assertSame([], $result['created'], 'a carried-over to-do is not new, so it is not re-snapshotted');
        $this->assertCount(1, $result['moved']);
        [$from, $to] = $result['moved'][0];
        $this->assertSame($shared, $from);
        $this->assertNotSame($shared, $to);
        $this->assertFalse($letterInstance->getLetterInstanceTodos()->contains($shared));
        $this->assertFalse($a->getLetterInstanceTodos()->contains($shared));
        $this->assertSame([$to], array_values($letterInstance->getLetterInstanceTodos()->toArray()));
        $this->assertSame($b, $to->getLetterInstanceIssue());
        $this->assertTrue($b->getLetterInstanceTodos()->contains($to));
        $this->assertSame($t1, $to->getLetterTodoVersion());
        $this->assertSame($shared->getEditedDescription(), $to->getEditedDescription());
        $this->assertSame($shared->getGeneratedDescription(), $to->getGeneratedDescription());
        $this->assertSame(0, $to->getIsRendered());
    }

    public function testReconcileRemovesTodosNoIssueBringsAnyMore(): void
    {
        $t1 = $this->todoVersion(1);
        $t2 = $this->todoVersion(2);
        $letterInstance = new LetterInstance();
        $issue = $this->instanceIssue($letterInstance, 0, [$t1]);
        $this->existingTodo($letterInstance, $issue, $t1);
        $orphan = $this->existingTodo($letterInstance, $issue, $t2);

        $this->sut->reconcileTodos($letterInstance);

        $this->assertSame([1], $this->todoVersionIds($letterInstance));
        $this->assertFalse($issue->getLetterInstanceTodos()->contains($orphan));
    }

    public function testReconcileAddsTodosForNewIssuesOnTheCurrentVersion(): void
    {
        $todo = $this->todoWithCurrentVersion(4, 40);
        $letterInstance = new LetterInstance();
        $issue = $this->instanceIssue($letterInstance, 0, [$this->todoVersionOf($todo, 39)]);

        $created = $this->sut->reconcileTodos($letterInstance)['created'];

        $this->assertCount(1, $created);
        $this->assertSame(40, $created[0]->getLetterTodoVersion()->getId());
        $this->assertSame($issue, $created[0]->getLetterInstanceIssue());
        $this->assertTrue($issue->getLetterInstanceTodos()->contains($created[0]));
        $this->assertSame([40], $this->todoVersionIds($letterInstance));
    }

    public function testReconcileUsesDisplayOrderNotCollectionOrder(): void
    {
        $t1 = $this->todoVersion(1);
        $letterInstance = new LetterInstance();
        $this->instanceIssue($letterInstance, 1, [$t1]);
        $a = $this->instanceIssue($letterInstance, 0, [$t1]);

        $created = $this->sut->reconcileTodos($letterInstance)['created'];

        $this->assertSame($a, $created[0]->getLetterInstanceIssue());
    }

    /**
     * Letters generated before VOL-7408 can hold two rows for the same to-do. Neither may be
     * thrown away: one of them may carry the caseworker's edit.
     */
    public function testReconcileKeepsLegacyDuplicateRowsForTheSameTodo(): void
    {
        $t1 = $this->todoVersion(1);
        $letterInstance = new LetterInstance();
        $a = $this->instanceIssue($letterInstance, 0, [$t1]);
        $b = $this->instanceIssue($letterInstance, 1, [$t1]);
        $first = $this->existingTodo($letterInstance, $a, $t1);
        $duplicate = $this->existingTodo($letterInstance, $b, $t1);
        $duplicate->setEditedDescription(['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'dup edit']]]]);

        $result = $this->sut->reconcileTodos($letterInstance);

        $todos = array_values($letterInstance->getLetterInstanceTodos()->toArray());
        $this->assertCount(2, $todos);
        $this->assertContains($first, $todos);
        $this->assertSame($a, $first->getLetterInstanceIssue());
        [$from, $to] = $result['moved'][0];
        $this->assertSame($duplicate, $from);
        $this->assertSame($a, $to->getLetterInstanceIssue());
        $this->assertSame('dup edit', $to->getEditedDescription()['blocks'][0]['data']['text']);
    }

    public function testApplyIssueChangeRenumbersIssuesAndReturnsNewRows(): void
    {
        $shared = $this->todoVersion(1);
        $letterInstance = new LetterInstance();
        $a = $this->instanceIssue($letterInstance, 0, [$this->todoVersion(2)]);
        $b = $this->instanceIssue($letterInstance, 1, [$shared]);
        $edited = $this->existingTodo($letterInstance, $b, $shared);
        $edited->setEditedDescription(['blocks' => []]);
        $cVersion = $this->issueVersionWithTodos([$this->todoVersion(3)]);

        $created = $this->sut->applyIssueChange($letterInstance, new IssueChangePlan([$cVersion, $b], [$a], []));
        $this->assertSame([], $created['movedTodos'], "B's to-do stays with B");

        $issues = $letterInstance->getLetterInstanceIssues();
        $this->assertCount(2, $issues);
        $this->assertFalse($issues->contains($a));
        $this->assertCount(1, $created['issues']);
        $c = $created['issues'][0];
        $this->assertSame($cVersion, $c->getLetterIssueVersion());
        $this->assertSame(0, $c->getDisplayOrder());
        $this->assertSame(1, $b->getDisplayOrder());
        $this->assertCount(1, $created['todos']);
        $this->assertSame($c, $created['todos'][0]->getLetterInstanceIssue());
        $this->assertSame($b, $edited->getLetterInstanceIssue());
        $this->assertNotNull($edited->getEditedDescription());
        $ids = $this->todoVersionIds($letterInstance);
        sort($ids);
        $this->assertSame([1, 3], $ids, "A's to-do goes with A; C's arrives; B's shared one stays");
    }

    public function testComposesAppendicesInOrder(): void
    {
        $letterInstance = new LetterInstance();

        $first = m::mock(LetterAppendixVersion::class)->makePartial();
        $second = m::mock(LetterAppendixVersion::class)->makePartial();

        $this->sut->composeAppendices($letterInstance, [$first, $second]);

        $appendices = $letterInstance->getLetterInstanceAppendices();
        $this->assertCount(2, $appendices);
        $this->assertSame($first, $appendices->first()->getLetterAppendixVersion());
        $this->assertSame([0, 1], array_map(
            static fn($a): int => $a->getDisplayOrder(),
            $appendices->toArray()
        ));
    }
}
