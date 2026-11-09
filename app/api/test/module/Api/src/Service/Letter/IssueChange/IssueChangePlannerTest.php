<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Letter\IssueChange;

use Dvsa\Olcs\Api\Entity\Letter\LetterInstance;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceTodo;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssue;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueTodo;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion;
use Dvsa\Olcs\Api\Entity\Letter\LetterTodo;
use Dvsa\Olcs\Api\Entity\Letter\LetterTodoVersion;
use Dvsa\Olcs\Api\Service\Letter\IssueChange\IssueChangePlanner;
use Mockery as m;
use PHPUnit\Framework\TestCase;

/**
 * Works out what changing a letter's issues would do, without changing anything (VOL-7734).
 */
class IssueChangePlannerTest extends TestCase
{
    private IssueChangePlanner $sut;

    protected function setUp(): void
    {
        $this->sut = new IssueChangePlanner();
    }

    protected function tearDown(): void
    {
        m::close();
    }

    private function todoVersion(int $todoId, string $name): LetterTodoVersion
    {
        $todo = m::mock(LetterTodo::class)->makePartial();
        $todo->setId($todoId);
        $todo->setTodoKey('TD' . $todoId);

        $todoVersion = m::mock(LetterTodoVersion::class)->makePartial();
        $todoVersion->setId($todoId * 10);
        $todoVersion->setLetterTodo($todo);
        $todoVersion->setName($name);
        $todo->setCurrentVersion($todoVersion);

        return $todoVersion;
    }

    /**
     * @param LetterTodoVersion[] $todoVersions
     */
    private function issueVersion(LetterIssue $issue, string $heading, array $todoVersions): LetterIssueVersion
    {
        $issueTodos = [];
        foreach ($todoVersions as $todoVersion) {
            $issueTodo = m::mock(LetterIssueTodo::class)->makePartial();
            $issueTodo->shouldReceive('getLetterTodoVersion')->andReturn($todoVersion);
            $issueTodos[] = $issueTodo;
        }

        $issueVersion = m::mock(LetterIssueVersion::class)->makePartial();
        $issueVersion->shouldReceive('getLetterIssueTodos')->andReturn($issueTodos);
        $issueVersion->setLetterIssue($issue);
        $issueVersion->setHeading($heading);

        return $issueVersion;
    }

    /**
     * @param LetterTodoVersion[] $todoVersions
     */
    private function issue(int $id, array $todoVersions = []): LetterIssue
    {
        $issue = m::mock(LetterIssue::class)->makePartial();
        $issue->setId($id);
        $issue->setCurrentVersion($this->issueVersion($issue, 'Issue ' . $id, $todoVersions));

        return $issue;
    }

    private function onLetter(LetterInstance $letterInstance, LetterIssueVersion $version, int $displayOrder): LetterInstanceIssue
    {
        $instanceIssue = new LetterInstanceIssue();
        $instanceIssue->setLetterIssueVersion($version);
        $instanceIssue->setDisplayOrder($displayOrder);
        $letterInstance->addLetterInstanceIssue($instanceIssue);

        return $instanceIssue;
    }

    private function todoOnLetter(LetterInstance $letterInstance, LetterInstanceIssue $owner, LetterTodoVersion $version): LetterInstanceTodo
    {
        $instanceTodo = new LetterInstanceTodo();
        $instanceTodo->setLetterTodoVersion($version);
        $owner->addLetterInstanceTodo($instanceTodo);
        $letterInstance->addLetterInstanceTodo($instanceTodo);

        return $instanceTodo;
    }

    public function testKeptIssueIsMatchedByParentIssueAndKeepsItsPinnedVersion(): void
    {
        $issue = $this->issue(7);
        $pinned = $this->issueVersion($issue, 'Old wording', []);
        $letterInstance = new LetterInstance();
        $row = $this->onLetter($letterInstance, $pinned, 0);

        $plan = $this->sut->plan($letterInstance, [$issue]);

        $this->assertSame([$row], $plan->finalIssues);
        $this->assertSame([], $plan->removedIssues);
    }

    public function testUnselectedIssueIsRemovedAndNewOneAdded(): void
    {
        $seven = $this->issue(7);
        $nine = $this->issue(9);
        $letterInstance = new LetterInstance();
        $row = $this->onLetter($letterInstance, $seven->getCurrentVersion(), 0);

        $plan = $this->sut->plan($letterInstance, [$nine]);

        $this->assertSame([$row], $plan->removedIssues);
        $this->assertSame([$nine->getCurrentVersion()], $plan->finalIssues);
    }

    public function testLostTodosAreThoseNoFinalIssueBrings(): void
    {
        $t1 = $this->todoVersion(1, 'Send bank statements');
        $t2 = $this->todoVersion(2, 'Send a lease');
        $seven = $this->issue(7, [$t1, $t2]);
        $nine = $this->issue(9, [$t2]);
        $letterInstance = new LetterInstance();
        $row = $this->onLetter($letterInstance, $seven->getCurrentVersion(), 0);
        $lost = $this->todoOnLetter($letterInstance, $row, $t1);
        $this->todoOnLetter($letterInstance, $row, $t2);

        $plan = $this->sut->plan($letterInstance, [$nine]);

        $this->assertSame([$lost], $plan->lostTodos);
    }

    public function testLostEditsListsEditedRemovedIssuesAndEditedLostTodos(): void
    {
        $t1 = $this->todoVersion(1, 'Send bank statements');
        $t2 = $this->todoVersion(2, 'Send a lease');
        $seven = $this->issue(7, [$t1]);
        $eight = $this->issue(8, [$t2]);
        $keep = $this->issue(9);
        $letterInstance = new LetterInstance();
        $editedIssue = $this->onLetter($letterInstance, $seven->getCurrentVersion(), 0);
        $editedIssue->setEditedContent(['blocks' => []]);
        $this->todoOnLetter($letterInstance, $editedIssue, $t1)->setEditedDescription(['blocks' => []]);
        $plainIssue = $this->onLetter($letterInstance, $eight->getCurrentVersion(), 1);
        $this->todoOnLetter($letterInstance, $plainIssue, $t2);
        $this->onLetter($letterInstance, $keep->getCurrentVersion(), 2);

        $plan = $this->sut->plan($letterInstance, [$keep]);

        $this->assertSame(['Issue 7', 'Send bank statements'], $plan->lostEdits());
    }

    public function testLostTodoWithoutANameIsListedByItsKey(): void
    {
        $t1 = $this->todoVersion(1, '');
        $seven = $this->issue(7, [$t1]);
        $letterInstance = new LetterInstance();
        $row = $this->onLetter($letterInstance, $seven->getCurrentVersion(), 0);
        $this->todoOnLetter($letterInstance, $row, $t1)->setEditedDescription(['blocks' => []]);

        $plan = $this->sut->plan($letterInstance, []);

        $this->assertSame(['TD1'], $plan->lostEdits());
    }

    public function testSameSelectionInSameOrderHasNoChanges(): void
    {
        $seven = $this->issue(7);
        $nine = $this->issue(9);
        $letterInstance = new LetterInstance();
        $this->onLetter($letterInstance, $seven->getCurrentVersion(), 0);
        $this->onLetter($letterInstance, $nine->getCurrentVersion(), 1);

        $this->assertFalse($this->sut->plan($letterInstance, [$seven, $nine])->hasChanges());
    }

    public function testSameIssuesInADifferentOrderKeepTheLettersOrder(): void
    {
        $seven = $this->issue(7);
        $nine = $this->issue(9);
        $letterInstance = new LetterInstance();
        $sevenRow = $this->onLetter($letterInstance, $seven->getCurrentVersion(), 0);
        $nineRow = $this->onLetter($letterInstance, $nine->getCurrentVersion(), 1);

        $plan = $this->sut->plan($letterInstance, [$nine, $seven]);

        $this->assertFalse($plan->hasChanges());
        $this->assertSame([$sevenRow, $nineRow], $plan->finalIssues);
    }

    public function testAddingAnIssueUsesTheSelectionOrder(): void
    {
        $seven = $this->issue(7);
        $nine = $this->issue(9);
        $eleven = $this->issue(11);
        $letterInstance = new LetterInstance();
        $sevenRow = $this->onLetter($letterInstance, $seven->getCurrentVersion(), 0);
        $nineRow = $this->onLetter($letterInstance, $nine->getCurrentVersion(), 1);

        $plan = $this->sut->plan($letterInstance, [$nine, $eleven, $seven]);

        $this->assertTrue($plan->hasChanges());
        $this->assertSame([$nineRow, $eleven->getCurrentVersion(), $sevenRow], $plan->finalIssues);
    }
}
