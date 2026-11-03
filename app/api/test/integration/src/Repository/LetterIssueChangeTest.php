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
 * cascades its removal to its to-dos. A to-do that moves from a removed issue to a remaining one has
 * to survive all of that with its edit intact; only a real flush shows whether it does.
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
        $todoRow = $this->instanceTodo($instanceId, $rowA, $todoVersion, self::EDIT);

        $this->change($instanceId, [$issueB]);

        $this->assertSame(0, $this->rows('letter_instance_issue', 'id', $rowA));
        $moved = $this->conn()->fetchAssociative('SELECT * FROM letter_instance_todo WHERE id = ?', [$todoRow]);
        $this->assertIsArray($moved, 'the shared to-do row must survive its first issue being removed');
        $this->assertSame($rowB, (int) $moved['letter_instance_issue_id']);
        // MySQL's JSON type reorders object keys, so compare by content, not key order
        $this->assertEquals(json_decode(self::EDIT, true), json_decode((string) $moved['edited_description'], true));
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

    private function issue(): array
    {
        $categoryId = $this->conn()->fetchOne('SELECT MIN(id) FROM category');

        return $this->versioned('letter_issue', ['issue_key' => $this->key()], ['heading' => 'Issue', 'category_id' => $categoryId]);
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

    private function instanceTodo(int $instanceId, int $instanceIssueId, int $todoVersionId, ?string $edited): int
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
