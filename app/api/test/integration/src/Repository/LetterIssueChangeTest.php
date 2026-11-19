<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Integration\Repository;

use Doctrine\DBAL\Connection;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance;
use Dvsa\Olcs\Api\Service\Letter\IssueChange\IssueChangePlanner;
use Dvsa\Olcs\Api\Service\Letter\LetterInstanceComposer;
use Dvsa\OlcsTest\Integration\IntegrationTestCase;

/**
 * Changing the issues on a generated letter (VOL-7734), flushed through the real mappings.
 *
 * Instance to-dos sit in two orphanRemoval collections (the letter's and their issue's) and an issue
 * cascades its removal to its to-dos. A to-do whose issue changes is carried over to a new row, and
 * its edit has to come through all of that intact, whether its new issue was already on the letter
 * or has just been added; only a real flush shows whether it does.
 */
final class LetterIssueChangeTest extends IntegrationTestCase
{
    private const NOW = '2026-11-03 12:00:00';
    private const EDIT = '{"blocks":[{"type":"paragraph","data":{"text":"caseworker edit"}}]}';

    public function testSharedEditedTodoSurvivesItsOwnerBeingRemoved(): void
    {
        [, [$versionA]] = $this->issue();
        [$issueB, [$versionB]] = $this->issue();
        [, [$todoVersion]] = $this->todo();
        $this->linkToDo($versionA, $todoVersion);
        $this->linkToDo($versionB, $todoVersion);

        $instanceId = $this->letterInstance();
        $rowA = $this->instanceIssue($instanceId, $versionA, 0);
        $rowB = $this->instanceIssue($instanceId, $versionB, 1);
        $this->instanceTodo($instanceId, $rowA, $todoVersion, self::EDIT);

        $this->change($instanceId, [$issueB]);

        $this->assertSame(0, $this->rows('letter_instance_issue', 'id', $rowA));
        $this->assertSame(1, $this->rows('letter_instance_issue', 'id', $rowB));
        $this->assertEditedTodoOwnedBy($instanceId, $versionB);
    }

    public function testTodoOnlyTheRemovedIssueBroughtIsDeleted(): void
    {
        [, [$versionA]] = $this->issue();
        [$issueB, [$versionB]] = $this->issue();
        [, [$onlyA]] = $this->todo();
        $this->linkToDo($versionA, $onlyA);

        $instanceId = $this->letterInstance();
        $rowA = $this->instanceIssue($instanceId, $versionA, 0);
        $this->instanceIssue($instanceId, $versionB, 1);
        $todoRow = $this->instanceTodo($instanceId, $rowA, $onlyA, null);

        $this->change($instanceId, [$issueB]);

        $this->assertSame(0, $this->rows('letter_instance_todo', 'id', $todoRow));
        $this->assertSame(1, $this->rows('letter_instance_issue', 'letter_instance_id', $instanceId));
    }

    public function testAddedIssueGetsItsTodoRows(): void
    {
        [$issueA, [$versionA]] = $this->issue();
        [$issueB, [$versionB]] = $this->issue();
        [, [$todoVersion]] = $this->todo();
        $this->linkToDo($versionB, $todoVersion);

        $instanceId = $this->letterInstance();
        $this->instanceIssue($instanceId, $versionA, 0);

        $this->change($instanceId, [$issueA, $issueB]);

        $rowB = (int) $this->conn()->fetchOne(
            'SELECT id FROM letter_instance_issue WHERE letter_instance_id = ? AND letter_issue_version_id = ?',
            [$instanceId, $versionB]
        );
        $this->assertGreaterThan(0, $rowB);
        $this->assertSame(1, $this->rows('letter_instance_todo', 'letter_instance_issue_id', $rowB));
    }

    /**
     * Swap: the only issue owning an edited to-do is replaced by a newly added one that brings the
     * same to-do. Nothing is lost, so nobody is asked, and the edit must still be there.
     */
    public function testEditedTodoSurvivesMovingOntoANewlyAddedIssue(): void
    {
        [, [$versionA]] = $this->issue();
        [$issueC, [$versionC]] = $this->issue();
        [, [$todoVersion]] = $this->todo();
        $this->linkToDo($versionA, $todoVersion);
        $this->linkToDo($versionC, $todoVersion);

        $instanceId = $this->letterInstance();
        $rowA = $this->instanceIssue($instanceId, $versionA, 0);
        $this->instanceTodo($instanceId, $rowA, $todoVersion, self::EDIT);

        $this->change($instanceId, [$issueC]);

        $this->assertEditedTodoOwnedBy($instanceId, $versionC);
    }

    /**
     * Add only: a newly ticked issue that sits earlier in the letter takes over a to-do an existing
     * issue owns. No issue is removed, and the edit must survive.
     */
    public function testEditedTodoSurvivesANewIssueAheadOfItsOwner(): void
    {
        [$issueB, [$versionB]] = $this->issue();
        [$issueC, [$versionC]] = $this->issue();
        [, [$todoVersion]] = $this->todo();
        $this->linkToDo($versionB, $todoVersion);
        $this->linkToDo($versionC, $todoVersion);

        $instanceId = $this->letterInstance();
        $rowB = $this->instanceIssue($instanceId, $versionB, 0);
        $this->instanceTodo($instanceId, $rowB, $todoVersion, self::EDIT);

        $this->change($instanceId, [$issueC, $issueB]);

        $this->assertEditedTodoOwnedBy($instanceId, $versionC);
    }

    /**
     * VOL-7735: removing an Other issue takes the caseworker's own to-dos with it.
     */
    public function testRemovingAnOtherIssueWithCustomTodosSaves(): void
    {
        [, [$otherVersion]] = $this->issue(true);
        [$issueB, [$versionB]] = $this->issue();

        $instanceId = $this->letterInstance();
        $otherRow = $this->instanceIssue($instanceId, $otherVersion, 0);
        $this->instanceIssue($instanceId, $versionB, 1);
        $customRow = $this->instanceTodo($instanceId, $otherRow, null, self::EDIT);

        $this->change($instanceId, [$issueB]);

        $this->assertSame(0, $this->rows('letter_instance_todo', 'id', $customRow));
        $this->assertSame(0, $this->rows('letter_instance_issue', 'id', $otherRow));
    }

    /**
     * VOL-7735: adding issues leaves an Other issue's own to-dos where they are, edits intact.
     */
    public function testAddingIssuesKeepsCustomTodos(): void
    {
        [$otherIssue, [$otherVersion]] = $this->issue(true);
        [$issueC, [$versionC]] = $this->issue();
        [, [$todoVersion]] = $this->todo();
        $this->linkToDo($versionC, $todoVersion);

        $instanceId = $this->letterInstance();
        $otherRow = $this->instanceIssue($instanceId, $otherVersion, 0);
        $customRow = $this->instanceTodo($instanceId, $otherRow, null, self::EDIT);

        $this->change($instanceId, [$issueC, $otherIssue]);

        $row = $this->conn()->fetchAssociative('SELECT * FROM letter_instance_todo WHERE id = ?', [$customRow]);
        $this->assertIsArray($row, "the Other issue's own to-do must still be there");
        $this->assertSame($otherRow, (int) $row['letter_instance_issue_id']);
        $this->assertNull($row['letter_todo_version_id']);
        $this->assertEquals(json_decode(self::EDIT, true), json_decode((string) $row['edited_description'], true));
        $this->assertSame(2, $this->rows('letter_instance_todo', 'letter_instance_id', $instanceId));
    }

    private function assertEditedTodoOwnedBy(int $instanceId, int $issueVersionId): void
    {
        $rows = $this->conn()->fetchAllAssociative(
            'SELECT t.edited_description, i.letter_issue_version_id FROM letter_instance_todo t'
            . ' JOIN letter_instance_issue i ON i.id = t.letter_instance_issue_id WHERE t.letter_instance_id = ?',
            [$instanceId]
        );

        $this->assertCount(1, $rows, 'the to-do both issues bring must still be on the letter, once');
        $this->assertSame($issueVersionId, (int) $rows[0]['letter_issue_version_id']);
        // MySQL's JSON type reorders object keys, so compare by content, not key order
        $this->assertEquals(json_decode(self::EDIT, true), json_decode((string) $rows[0]['edited_description'], true));
    }

    /**
     * Load the letter fresh, apply the change, flush, and forget the managed state so assertions
     * read what is really in the database.
     *
     * @param list<int> $issueIds
     */
    private function change(int $instanceId, array $issueIds): void
    {
        $this->em()->clear();

        /** @var LetterInstance $letterInstance */
        $letterInstance = $this->repo('LetterInstance')->fetchById($instanceId);
        $issues = array_map(fn(int $id) => $this->repo('LetterIssue')->fetchById($id), $issueIds);

        $plan = (new IssueChangePlanner())->plan($letterInstance, $issues);
        (new LetterInstanceComposer())->applyIssueChange($letterInstance, $plan);

        $this->em()->flush();
        $this->em()->clear();
    }

    private function conn(): Connection
    {
        return $this->em()->getConnection();
    }

    private function create(string $table, array $row): int
    {
        $this->conn()->insert($table, $row);

        return (int) $this->conn()->lastInsertId();
    }

    private function rows(string $table, string $column, int $value): int
    {
        return (int) $this->conn()->fetchOne("SELECT COUNT(*) FROM {$table} WHERE {$column} = ?", [$value]);
    }

    private function key(): string
    {
        return 'itest-7734-' . uniqid();
    }

    /**
     * @return array{int, list<int>} the parent id and its version id
     */
    private function versioned(string $table, array $row, array $versionRow): array
    {
        $id = $this->create($table, $row);
        $versionId = $this->create($table . '_version', $versionRow + [
            $table . '_id' => $id,
            'version_number' => 1,
            'created_on' => self::NOW,
        ]);
        $this->conn()->update($table, ['current_version_id' => $versionId], ['id' => $id]);

        return [$id, [$versionId]];
    }

    private function issue(bool $isOther = false): array
    {
        $categoryId = $this->conn()->fetchOne('SELECT MIN(id) FROM category');

        return $this->versioned(
            'letter_issue',
            ['issue_key' => $this->key()],
            ['heading' => $isOther ? 'Other issue' : 'Issue', 'category_id' => $categoryId, 'is_other' => (int) $isOther]
        );
    }

    private function todo(): array
    {
        return $this->versioned('letter_todo', ['todo_key' => $this->key()], []);
    }

    private function linkToDo(int $issueVersionId, int $todoVersionId): void
    {
        $this->conn()->insert('letter_issue_todo', [
            'letter_issue_version_id' => $issueVersionId,
            'letter_todo_version_id' => $todoVersionId,
            'display_order' => 1,
        ]);
    }

    private function letterInstance(): int
    {
        $licence = $this->conn()->fetchAssociative(
            'SELECT id, organisation_id FROM licence WHERE organisation_id IS NOT NULL ORDER BY id LIMIT 1'
        );
        $this->assertIsArray($licence, 'Expected the seeded test dataset to contain a licence');

        return $this->create('letter_instance', [
            'reference' => substr('IT' . uniqid(), 0, 20),
            'letter_type_id' => $this->create('letter_type', ['name' => 'ITEST 7734']),
            'licence_id' => $licence['id'],
            'organisation_id' => $licence['organisation_id'],
            'status' => 'ltr_sts_draft',
        ]);
    }

    private function instanceIssue(int $instanceId, int $issueVersionId, int $displayOrder): int
    {
        return $this->create('letter_instance_issue', [
            'letter_instance_id' => $instanceId,
            'letter_issue_version_id' => $issueVersionId,
            'display_order' => $displayOrder,
        ]);
    }

    private function instanceTodo(int $instanceId, int $instanceIssueId, ?int $todoVersionId, ?string $edited): int
    {
        return $this->create('letter_instance_todo', [
            'letter_instance_id' => $instanceId,
            'letter_instance_issue_id' => $instanceIssueId,
            'letter_todo_version_id' => $todoVersionId,
            'edited_description' => $edited,
            'display_order' => 0,
        ]);
    }
}
