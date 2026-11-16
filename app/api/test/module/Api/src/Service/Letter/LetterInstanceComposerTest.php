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
use Dvsa\Olcs\Api\Entity\Letter\LetterIssue;

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

    /**
     * An Other issue with its caseworker-written to-dos, in the given display orders (VOL-7735)
     *
     * @param list<int> $todoOrders
     * @return array{0: LetterInstanceIssue, 1: list<LetterInstanceTodo>}
     */
    private function otherIssueWithCustomTodos(LetterInstance $letterInstance, int $displayOrder, array $todoOrders): array
    {
        $version = $this->issueVersionWithTodos([]);
        $version->setIsOther(true);
        $version->setHeading('Other issue');
        $issue = new LetterInstanceIssue();
        $issue->setLetterIssueVersion($version);
        $issue->setDisplayOrder($displayOrder);
        $letterInstance->addLetterInstanceIssue($issue);

        $todos = [];
        foreach ($todoOrders as $order) {
            $todo = new LetterInstanceTodo();
            $todo->setDisplayOrder($order);
            $issue->addLetterInstanceTodo($todo);
            $letterInstance->addLetterInstanceTodo($todo);
            $todos[] = $todo;
        }

        return [$issue, $todos];
    }

    public function testReconcileLeavesCustomTodosAlone(): void
    {
        $letterInstance = new LetterInstance();
        $a = $this->instanceIssue($letterInstance, 0, [$this->todoVersion(1)]);
        [$other, [$first, $second]] = $this->otherIssueWithCustomTodos($letterInstance, 1, [5, 9]);
        $first->setEditedDescription(['blocks' => [['type' => 'paragraph', 'data' => ['text' => 'Send the lease']]]]);

        $this->sut->reconcileTodos($letterInstance);

        $this->assertTrue($letterInstance->getLetterInstanceTodos()->contains($first));
        $this->assertTrue($letterInstance->getLetterInstanceTodos()->contains($second));
        $this->assertSame($other, $first->getLetterInstanceIssue());
        $this->assertNotNull($first->getEditedDescription());
        $this->assertCount(3, $letterInstance->getLetterInstanceTodos(), "A's library to-do arrives too");
        // numbered in letter order: A's to-do, then the Other issue's in the order written
        $this->assertSame([1, 2], [$first->getDisplayOrder(), $second->getDisplayOrder()]);
        $this->assertSame($a, $letterInstance->getLetterInstanceTodos()->filter(
            static fn(LetterInstanceTodo $t): bool => !$t->isCustom()
        )->first()->getLetterInstanceIssue());
    }

    public function testRemovingAnOtherIssueRemovesItsCustomTodos(): void
    {
        $letterInstance = new LetterInstance();
        $keep = $this->instanceIssue($letterInstance, 0, []);
        [$other, [$custom]] = $this->otherIssueWithCustomTodos($letterInstance, 1, [0]);

        $this->sut->applyIssueChange($letterInstance, new IssueChangePlan([$keep], [$other], [$custom]));

        $this->assertFalse($letterInstance->getLetterInstanceTodos()->contains($custom));
        $this->assertFalse($letterInstance->getLetterInstanceIssues()->contains($other));
    }

    /**
     * Renumbering follows letter order (each issue's library to-dos, then its own) and leaves a
     * to-do with no issue on the letter where it was relative to the rest, last (VOL-7735)
     */
    public function testRenumberFollowsLetterOrderWithoutAddingOrDropping(): void
    {
        $letterInstance = new LetterInstance();
        $second = $this->instanceIssue($letterInstance, 1, []);
        $first = $this->instanceIssue($letterInstance, 0, []);

        $custom = new LetterInstanceTodo();
        $custom->setDisplayOrder(0);
        $first->addLetterInstanceTodo($custom);
        $letterInstance->addLetterInstanceTodo($custom);
        $library = $this->existingTodo($letterInstance, $first, $this->todoVersion(1));
        $library->setDisplayOrder(5);
        $later = $this->existingTodo($letterInstance, $second, $this->todoVersion(2));
        $later->setDisplayOrder(1);
        $loose = new LetterInstanceTodo();
        $loose->setLetterTodoVersion($this->todoVersion(3));
        $loose->setDisplayOrder(2);
        $letterInstance->addLetterInstanceTodo($loose);

        $this->sut->renumberTodos($letterInstance);

        $this->assertCount(4, $letterInstance->getLetterInstanceTodos());
        $this->assertSame(
            [0, 1, 2, 3],
            [$library->getDisplayOrder(), $custom->getDisplayOrder(), $later->getDisplayOrder(), $loose->getDisplayOrder()]
        );
    }

    private function paragraph(string $text): array
    {
        return ['blocks' => [['type' => 'paragraph', 'data' => ['text' => $text]]]];
    }

    /**
     * An issue on a letter, by the id of the library issue it came from. Every call makes its own
     * version, as a letter made later can be on a newer version of the same issue.
     *
     * @param LetterTodoVersion[] $todoVersions
     */
    private function issueFromLibrary(
        LetterInstance $letterInstance,
        int $issueId,
        int $displayOrder,
        array $todoVersions = [],
        bool $isOther = false
    ): LetterInstanceIssue {
        $libraryIssue = m::mock(LetterIssue::class)->makePartial();
        $libraryIssue->setId($issueId);

        $version = $this->issueVersionWithTodos($todoVersions);
        $version->setLetterIssue($libraryIssue);
        $version->setDefaultBodyContent($this->paragraph('Library wording'));
        $version->setIsOther($isOther);

        $instanceIssue = new LetterInstanceIssue();
        $instanceIssue->setLetterIssueVersion($version);
        $instanceIssue->setDisplayOrder($displayOrder);
        $instanceIssue->setGeneratedContent($this->paragraph('Library wording'));
        $letterInstance->addLetterInstanceIssue($instanceIssue);

        return $instanceIssue;
    }

    /**
     * The library issues behind some of a letter's issues. These are compared, not the issues
     * themselves: when a comparison of entities fails, PHPUnit writes out everything they link
     * to, mocks and all, and the run doesn't come back.
     *
     * @param list<LetterInstanceIssue> $instanceIssues
     * @return list<int>
     */
    private function libraryIssueIds(array $instanceIssues): array
    {
        return array_map(
            static fn(LetterInstanceIssue $issue): int => (int) $issue->getLetterIssueVersion()->getLetterIssue()->getId(),
            $instanceIssues
        );
    }

    /**
     * What an editor saves when nothing was typed in it: the same words, with its own time and ids
     */
    private function savedUntouched(string $text): array
    {
        return ['time' => 1763100000000, 'blocks' => [['id' => 'a1B2', 'type' => 'paragraph', 'data' => ['text' => $text]]]];
    }

    public function testStartingFromASentLetterBringsOverTheWordingChangedOnAnIssue(): void
    {
        $sentLetter = new LetterInstance();
        $this->issueFromLibrary($sentLetter, 7, 0)
            ->setEditedContentFromArray($this->paragraph('Reworded by the caseworker'));

        $letterInstance = new LetterInstance();
        $issue = $this->issueFromLibrary($letterInstance, 7, 0);

        $broughtOver = $this->sut->bringOverWording($letterInstance, $sentLetter);

        $this->assertSame($this->paragraph('Reworded by the caseworker'), $issue->getEditedContent());
        // the issues it changed are handed back, so the caseworker can be told to check them
        $this->assertSame([7], $this->libraryIssueIds($broughtOver));
        // what it was generated with stays, so the edit can still be told from it
        $this->assertSame($this->paragraph('Library wording'), $issue->getGeneratedContent());
    }

    /**
     * An issue nobody reworded has to stay as generated: the new letter then follows the library
     * as it is now and carries today's details, not the sent letter's
     */
    public function testAnIssueSavedButNotRewordedStaysAsGenerated(): void
    {
        $sentLetter = new LetterInstance();
        $this->issueFromLibrary($sentLetter, 7, 0)->setEditedContentFromArray($this->savedUntouched('Library wording'));

        $letterInstance = new LetterInstance();
        $issue = $this->issueFromLibrary($letterInstance, 7, 0);

        $broughtOver = $this->sut->bringOverWording($letterInstance, $sentLetter);

        $this->assertNull($issue->getEditedContent());
        $this->assertSame([], $this->libraryIssueIds($broughtOver));
    }

    public function testOnlyAnIssueOnBothLettersGetsWording(): void
    {
        $sentLetter = new LetterInstance();
        $this->issueFromLibrary($sentLetter, 7, 0)->setEditedContentFromArray($this->paragraph('Seven, reworded'));
        $this->issueFromLibrary($sentLetter, 9, 1)->setEditedContentFromArray($this->paragraph('Nine, reworded'));

        $letterInstance = new LetterInstance();
        $added = $this->issueFromLibrary($letterInstance, 8, 0);
        $kept = $this->issueFromLibrary($letterInstance, 9, 1);

        $this->sut->bringOverWording($letterInstance, $sentLetter);

        $this->assertNull($added->getEditedContent());
        $this->assertSame($this->paragraph('Nine, reworded'), $kept->getEditedContent());
    }

    private function customTodo(LetterInstance $letterInstance, LetterInstanceIssue $issue, int $displayOrder, ?array $description): LetterInstanceTodo
    {
        $todo = new LetterInstanceTodo();
        $todo->setDisplayOrder($displayOrder);
        $todo->setEditedDescription($description);
        $issue->addLetterInstanceTodo($todo);
        $letterInstance->addLetterInstanceTodo($todo);

        return $todo;
    }

    /**
     * An Other issue is all the caseworker's own: its heading, its body and the to-dos they wrote
     */
    public function testAnOtherIssueBringsItsHeadingAndTheCaseworkersOwnTodos(): void
    {
        $sentLetter = new LetterInstance();
        $sentOther = $this->issueFromLibrary($sentLetter, 9, 0, [], true);
        $sentOther->setEditedHeading('Missing lease');
        $sentOther->setEditedContentFromArray($this->paragraph('We have not had the lease'));
        $written = $this->customTodo($sentLetter, $sentOther, 0, $this->paragraph('Send the lease'));
        $blank = $this->customTodo($sentLetter, $sentOther, 1, null);

        $letterInstance = new LetterInstance();
        $this->issueFromLibrary($letterInstance, 4, 0, [$this->todoVersion(1)]);
        $other = $this->issueFromLibrary($letterInstance, 9, 1, [], true);
        $this->sut->composeTodos($letterInstance);

        $broughtOver = $this->sut->bringOverWording($letterInstance, $sentLetter);

        $this->assertSame([9], $this->libraryIssueIds($broughtOver), 'once, however much of it came over');
        $this->assertSame('Missing lease', $other->getHeading());
        $this->assertSame($this->paragraph('We have not had the lease'), $other->getEditedContent());

        $custom = $other->getCustomTodos();
        $this->assertCount(1, $custom, 'a to-do with nothing written in it is left behind');
        $this->assertSame($this->paragraph('Send the lease'), $custom[0]->getEffectiveDescription());
        $this->assertTrue($letterInstance->getLetterInstanceTodos()->contains($custom[0]));
        // numbered in letter order, after the first issue's to-do
        $this->assertSame(1, $custom[0]->getDisplayOrder());

        // the sent letter keeps its own rows: moving one would delete it from that letter
        $this->assertNotSame($written, $custom[0]);
        $this->assertTrue([$written, $blank] === $sentOther->getCustomTodos());
        $this->assertCount(2, $sentLetter->getLetterInstanceTodos());
    }

    public function testAToDoRewordedOnTheSentLetterBringsItsWording(): void
    {
        $todo = $this->todoWithCurrentVersion(3, 31);

        $sentLetter = new LetterInstance();
        $sentIssue = $this->issueFromLibrary($sentLetter, 7, 0);
        // sent on an older version of the to-do than the library has now
        $reworded = $this->existingTodo($sentLetter, $sentIssue, $this->todoVersionOf($todo, 30));
        $reworded->setGeneratedDescription($this->paragraph('Send the lease'));
        $reworded->setEditedDescriptionFromArray($this->paragraph('Send the signed lease by Friday'));
        $untouched = $this->existingTodo($sentLetter, $sentIssue, $this->todoVersion(40));
        $untouched->setGeneratedDescription($this->paragraph('Pay the fee'));
        $untouched->setEditedDescriptionFromArray($this->savedUntouched('Pay the fee'));

        $letterInstance = new LetterInstance();
        $this->issueFromLibrary($letterInstance, 7, 0, [$todo->getCurrentVersion(), $this->todoVersion(40)]);
        $this->sut->composeTodos($letterInstance);

        // a to-do is reported as the issue it sits under
        $this->assertSame([7], $this->libraryIssueIds($this->sut->bringOverWording($letterInstance, $sentLetter)));

        [$first, $second] = array_values($letterInstance->getLetterInstanceTodos()->toArray());
        $this->assertSame(31, $first->getLetterTodoVersion()->getId());
        $this->assertSame($this->paragraph('Send the signed lease by Friday'), $first->getEditedDescription());
        $this->assertNull($second->getEditedDescription());
    }

    /**
     * A to-do's wording was written for the issues it sat under. A to-do that only reaches the
     * new letter through an issue the sent letter didn't have starts as the library has it.
     */
    public function testAToDoOnlyBringsItsWordingThroughAnIssueOnBothLetters(): void
    {
        $todo = $this->todoWithCurrentVersion(3, 31);

        $sentLetter = new LetterInstance();
        $sentIssue = $this->issueFromLibrary($sentLetter, 7, 0);
        $reworded = $this->existingTodo($sentLetter, $sentIssue, $todo->getCurrentVersion());
        $reworded->setGeneratedDescription($this->paragraph('Send the lease'));
        $reworded->setEditedDescriptionFromArray($this->paragraph('Send the lease for the yard in the issue above'));

        $letterInstance = new LetterInstance();
        $this->issueFromLibrary($letterInstance, 8, 0, [$todo->getCurrentVersion()]);
        $this->sut->composeTodos($letterInstance);

        $this->assertSame([], $this->libraryIssueIds($this->sut->bringOverWording($letterInstance, $sentLetter)));
        $this->assertNull($letterInstance->getLetterInstanceTodos()->first()->getEditedDescription());
    }

    /**
     * A shared to-do belongs to the first issue that brings it, which need not be the issue the
     * two letters have in common. It is reported as the issue the edit page shows it after.
     */
    public function testAToDoSharedWithAnIssueOnBothLettersBringsItsWordingWhicheverIssueItBelongsTo(): void
    {
        $todo = $this->todoWithCurrentVersion(3, 31);

        $sentLetter = new LetterInstance();
        $sentIssue = $this->issueFromLibrary($sentLetter, 7, 0);
        $reworded = $this->existingTodo($sentLetter, $sentIssue, $todo->getCurrentVersion());
        $reworded->setGeneratedDescription($this->paragraph('Send the lease'));
        $reworded->setEditedDescriptionFromArray($this->paragraph('Send the signed lease by Friday'));

        $letterInstance = new LetterInstance();
        $this->issueFromLibrary($letterInstance, 8, 0, [$todo->getCurrentVersion()]);
        $this->issueFromLibrary($letterInstance, 7, 1, [$todo->getCurrentVersion()]);
        $this->sut->composeTodos($letterInstance);

        // the row belongs to issue 8, the first to bring it; the edit page shows it after issue 7
        $this->assertSame([7], $this->libraryIssueIds($this->sut->bringOverWording($letterInstance, $sentLetter)));
        $this->assertSame(
            $this->paragraph('Send the signed lease by Friday'),
            $letterInstance->getLetterInstanceTodos()->first()->getEditedDescription()
        );
    }

    /**
     * Each part of an Other issue that comes over is enough on its own for the issue to be
     * named: its heading, or a to-do the caseworker wrote, with the body left as it started
     */
    public function testAnOtherIssueIsNamedWhenOnlyItsHeadingOrItsOwnTodosCameOver(): void
    {
        $headingOnly = new LetterInstance();
        $this->issueFromLibrary($headingOnly, 9, 0, [], true)->setEditedHeading('Missing lease');

        $todoOnly = new LetterInstance();
        $this->customTodo($todoOnly, $this->issueFromLibrary($todoOnly, 9, 0, [], true), 0, $this->paragraph('Send the lease'));

        foreach ([$headingOnly, $todoOnly] as $sentLetter) {
            $letterInstance = new LetterInstance();
            $other = $this->issueFromLibrary($letterInstance, 9, 0, [], true);

            $this->assertSame([9], $this->libraryIssueIds($this->sut->bringOverWording($letterInstance, $sentLetter)));
            $this->assertNull($other->getEditedContent());
        }
    }

    private function rewordedTodo(LetterInstance $letter, LetterInstanceIssue $under, LetterTodoVersion $version, string $text): LetterInstanceTodo
    {
        $todo = $this->existingTodo($letter, $under, $version);
        $todo->setGeneratedDescription($this->paragraph('Send the lease'));
        $todo->setEditedDescriptionFromArray($this->paragraph($text));

        return $todo;
    }

    /**
     * The to-do has to have come with that issue on the sent letter too. An issue can gain a
     * to-do in a new version between the two letters, and wording written under another issue
     * isn't its to bring. (A to-do added to the same version can't be told apart: see
     * LetterInstanceComposer::todosBroughtBy.)
     */
    public function testAToDoAnIssueGainedInANewVersionDoesNotBringWordingWrittenUnderAnotherIssue(): void
    {
        $todo = $this->todoWithCurrentVersion(3, 31);

        $sentLetter = new LetterInstance();
        $this->issueFromLibrary($sentLetter, 7, 0);
        $sentEight = $this->issueFromLibrary($sentLetter, 8, 1, [$todo->getCurrentVersion()]);
        $this->rewordedTodo($sentLetter, $sentEight, $todo->getCurrentVersion(), 'Send the lease for the yard in issue 8');

        $letterInstance = new LetterInstance();
        $this->issueFromLibrary($letterInstance, 7, 0, [$todo->getCurrentVersion()]);
        $this->sut->composeTodos($letterInstance);

        $this->assertSame([], $this->libraryIssueIds($this->sut->bringOverWording($letterInstance, $sentLetter)));
        $this->assertNull($letterInstance->getLetterInstanceTodos()->first()->getEditedDescription());
    }

    /**
     * Letters made before VOL-7408 hold a row for each issue that brought a to-do. The row that
     * sat under an issue is the one that speaks for it, reworded or not.
     */
    public function testWhereTheSentLetterHasARowForEachIssueTheIssuesOwnRowIsTheOneUsed(): void
    {
        $todo = $this->todoWithCurrentVersion(3, 31);
        $version = $todo->getCurrentVersion();

        $bothReworded = new LetterInstance();
        $seven = $this->issueFromLibrary($bothReworded, 7, 0, [$version]);
        $eight = $this->issueFromLibrary($bothReworded, 8, 1, [$version]);
        $this->rewordedTodo($bothReworded, $seven, $version, 'Reworded under 7');
        $this->rewordedTodo($bothReworded, $eight, $version, 'Reworded under 8');

        $letterInstance = new LetterInstance();
        $this->issueFromLibrary($letterInstance, 8, 0, [$version]);
        $this->sut->composeTodos($letterInstance);
        $this->sut->bringOverWording($letterInstance, $bothReworded);

        $this->assertSame(
            $this->paragraph('Reworded under 8'),
            $letterInstance->getLetterInstanceTodos()->first()->getEditedDescription()
        );

        // and where this issue's own row was left alone, another issue's rewording isn't borrowed
        $otherReworded = new LetterInstance();
        $seven = $this->issueFromLibrary($otherReworded, 7, 0, [$version]);
        $eight = $this->issueFromLibrary($otherReworded, 8, 1, [$version]);
        $this->existingTodo($otherReworded, $seven, $version)->setGeneratedDescription($this->paragraph('Send the lease'));
        $this->rewordedTodo($otherReworded, $eight, $version, 'Reworded under 8');

        $letterInstance = new LetterInstance();
        $this->issueFromLibrary($letterInstance, 7, 0, [$version]);
        $this->sut->composeTodos($letterInstance);

        $this->assertSame([], $this->libraryIssueIds($this->sut->bringOverWording($letterInstance, $otherReworded)));
        $this->assertNull($letterInstance->getLetterInstanceTodos()->first()->getEditedDescription());
    }

    /**
     * With a row for each issue on the sent letter and one row on the new one, the wording is
     * the first issue's, the one the new letter's row belongs to
     */
    public function testWhereSeveralIssuesHadTheirOwnRowTheFirstIssuesWordingIsUsed(): void
    {
        $version = $this->todoWithCurrentVersion(3, 31)->getCurrentVersion();

        $sentLetter = new LetterInstance();
        $this->rewordedTodo($sentLetter, $this->issueFromLibrary($sentLetter, 7, 0, [$version]), $version, 'Reworded under 7');
        $this->rewordedTodo($sentLetter, $this->issueFromLibrary($sentLetter, 8, 1, [$version]), $version, 'Reworded under 8');

        $letterInstance = new LetterInstance();
        $this->issueFromLibrary($letterInstance, 7, 0, [$version]);
        $this->issueFromLibrary($letterInstance, 8, 1, [$version]);
        $this->sut->composeTodos($letterInstance);

        // reported as issue 8, which the edit page shows it after
        $this->assertSame([8], $this->libraryIssueIds($this->sut->bringOverWording($letterInstance, $sentLetter)));
        $this->assertCount(1, $letterInstance->getLetterInstanceTodos());
        $this->assertSame(
            $this->paragraph('Reworded under 7'),
            $letterInstance->getLetterInstanceTodos()->first()->getEditedDescription()
        );
    }

    /**
     * The issue has to bring the to-do on the new letter as well. If the library has taken the
     * to-do off it since, the to-do is only there for another issue, and starts fresh.
     */
    public function testAToDoAnIssueNoLongerBringsDoesNotComeOverThroughAnotherIssue(): void
    {
        $version = $this->todoWithCurrentVersion(3, 31)->getCurrentVersion();

        $sentLetter = new LetterInstance();
        $this->rewordedTodo($sentLetter, $this->issueFromLibrary($sentLetter, 7, 0, [$version]), $version, 'Reworded under 7');

        $letterInstance = new LetterInstance();
        $this->issueFromLibrary($letterInstance, 7, 0);
        $this->issueFromLibrary($letterInstance, 8, 1, [$version]);
        $this->sut->composeTodos($letterInstance);

        $this->assertSame([], $this->libraryIssueIds($this->sut->bringOverWording($letterInstance, $sentLetter)));
        $this->assertNull($letterInstance->getLetterInstanceTodos()->first()->getEditedDescription());
    }

    /**
     * The usual case: two issues share a to-do, so the sent letter has one row for it, under
     * the first. A new letter with only the second issue still gets the wording.
     */
    public function testAToDoSharedByTwoIssuesComesOverWhenOnlyTheSecondIsOnTheNewLetter(): void
    {
        $version = $this->todoWithCurrentVersion(3, 31)->getCurrentVersion();

        $sentLetter = new LetterInstance();
        $first = $this->issueFromLibrary($sentLetter, 7, 0, [$version]);
        $this->issueFromLibrary($sentLetter, 8, 1, [$version]);
        $this->rewordedTodo($sentLetter, $first, $version, 'Send the signed lease by Friday');

        $letterInstance = new LetterInstance();
        $this->issueFromLibrary($letterInstance, 8, 0, [$version]);
        $this->sut->composeTodos($letterInstance);

        $this->assertSame([8], $this->libraryIssueIds($this->sut->bringOverWording($letterInstance, $sentLetter)));
        $this->assertSame(
            $this->paragraph('Send the signed lease by Friday'),
            $letterInstance->getLetterInstanceTodos()->first()->getEditedDescription()
        );
    }

    /**
     * An old letter can hold more than one row for a to-do under the same issue. The one that
     * was reworded is the one to copy, wherever it comes among them.
     */
    public function testOfSeveralRowsUnderOneIssueTheRewordedOneIsUsed(): void
    {
        $version = $this->todoWithCurrentVersion(3, 31)->getCurrentVersion();

        $sentLetter = new LetterInstance();
        $sentIssue = $this->issueFromLibrary($sentLetter, 7, 0, [$version]);
        $this->existingTodo($sentLetter, $sentIssue, $version)->setGeneratedDescription($this->paragraph('Send the lease'));
        $this->rewordedTodo($sentLetter, $sentIssue, $version, 'Send the signed lease by Friday');

        $letterInstance = new LetterInstance();
        $this->issueFromLibrary($letterInstance, 7, 0, [$version]);
        $this->sut->composeTodos($letterInstance);
        $this->sut->bringOverWording($letterInstance, $sentLetter);

        $this->assertSame(
            $this->paragraph('Send the signed lease by Friday'),
            $letterInstance->getLetterInstanceTodos()->first()->getEditedDescription()
        );
    }
}
