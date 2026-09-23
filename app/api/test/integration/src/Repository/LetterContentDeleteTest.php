<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Integration\Repository;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\Query;
use Dvsa\OlcsTest\Integration\IntegrationTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Usage checks, soft delete and hard delete on the letter content repositories (VOL-7494), run
 * against the real foreign keys. Fixtures are inserted inside the per-test transaction.
 */
final class LetterContentDeleteTest extends IntegrationTestCase
{
    private const NOW = '2026-09-23 12:00:00';

    public function testUnusedAppendixIsHardDeletedWithAllItsVersions(): void
    {
        [$id] = $this->appendix(2);
        $repo = $this->repo('LetterAppendix');

        $this->assertSame([], $repo->fetchLetterTypeNamesUsing($id));
        $this->assertFalse($repo->isUsedByLetterInstances($id));

        $entity = $repo->fetchById($id);
        $repo->hardDelete($id);

        $this->assertSame(0, $this->rows('letter_appendix', 'id', $id));
        $this->assertSame(0, $this->rows('letter_appendix_version', 'letter_appendix_id', $id));
        $this->assertFalse($this->em()->contains($entity), 'the deleted row should no longer be managed');
    }

    public function testAppendixOnALetterTypeNamesTheLetterTypes(): void
    {
        [$id, [$old, $current]] = $this->appendix(2);

        foreach ([$old => 'ITEST 7494 Beta', $current => 'ITEST 7494 Alpha'] as $versionId => $name) {
            $this->insert('letter_type_appendix', [
                'letter_type_id' => $this->letterType($name),
                'letter_appendix_version_id' => $versionId,
                'display_order' => 1,
            ]);
        }
        $this->letterType('ITEST 7494 Unrelated');

        $this->assertSame(
            ['ITEST 7494 Alpha', 'ITEST 7494 Beta'],
            $this->repo('LetterAppendix')->fetchLetterTypeNamesUsing($id),
        );
    }

    public function testAppendixOnAGeneratedLetterIsUsed(): void
    {
        [$id, [$old]] = $this->appendix(2);

        $this->insert('letter_instance_appendix', [
            'letter_instance_id' => $this->letterInstance(),
            'letter_appendix_version_id' => $old,
            'display_order' => 1,
        ]);

        $this->assertTrue($this->repo('LetterAppendix')->isUsedByLetterInstances($id));
    }

    public function testUnusedIssueIsHardDeletedWithItsToDoLinks(): void
    {
        [$id, $versionIds] = $this->issue(2);
        [$todoId, [$todoVersionId]] = $this->todo();

        foreach ($versionIds as $versionId) {
            $this->linkToDo($versionId, $todoVersionId);
        }

        $repo = $this->repo('LetterIssue');
        $this->assertSame([], $repo->fetchLetterTypeNamesUsing($id));
        $this->assertFalse($repo->isUsedByLetterInstances($id));

        $repo->hardDelete($id);

        $this->assertSame(0, $this->rows('letter_issue', 'id', $id));
        $this->assertSame(0, $this->rows('letter_issue_version', 'letter_issue_id', $id));
        $this->assertSame(0, $this->rows('letter_issue_todo', 'letter_todo_version_id', $todoVersionId));
        $this->assertSame(1, $this->rows('letter_todo', 'id', $todoId), 'the to-do itself stays');
    }

    public function testIssueOnALetterTypeNamesTheLetterTypes(): void
    {
        [$id, [$old]] = $this->issue(2);

        $this->insert('letter_type_issue', [
            'letter_type_id' => $this->letterType('ITEST 7494 Alpha'),
            'letter_issue_version_id' => $old,
        ]);

        $this->assertSame(['ITEST 7494 Alpha'], $this->repo('LetterIssue')->fetchLetterTypeNamesUsing($id));
    }

    public function testIssueOnAGeneratedLetterIsUsed(): void
    {
        [$id, [$old]] = $this->issue(2);

        $this->instanceIssue($this->letterInstance(), $old);

        $this->assertTrue($this->repo('LetterIssue')->isUsedByLetterInstances($id));
    }

    public function testUnusedToDoIsHardDeletedWithAllItsVersions(): void
    {
        [$id] = $this->todo(2);
        $repo = $this->repo('LetterTodo');

        $this->assertSame([], $repo->fetchLiveIssueKeysUsing($id));
        $this->assertFalse($repo->isReferenced($id));

        $repo->hardDelete($id);

        $this->assertSame(0, $this->rows('letter_todo', 'id', $id));
        $this->assertSame(0, $this->rows('letter_todo_version', 'letter_todo_id', $id));
    }

    public function testToDoOnTheCurrentVersionOfALiveIssueNamesTheIssueKeys(): void
    {
        [$id, [$old, $current]] = $this->todo(2);

        $keyB = $this->key('B');
        [, [$issueB]] = $this->issue(1, $keyB);
        $this->linkToDo($issueB, $old);

        $keyA = $this->key('A');
        [, [$issueA]] = $this->issue(1, $keyA);
        $this->linkToDo($issueA, $current);

        $this->assertSame([$keyA, $keyB], $this->repo('LetterTodo')->fetchLiveIssueKeysUsing($id));
    }

    public function testToDoOnlyOnDeletedIssuesOrOldIssueVersionsIsReferencedButNotLive(): void
    {
        [$id, [$todoVersionId]] = $this->todo();

        [$deletedIssueId, [$deletedIssueVersion]] = $this->issue();
        $this->conn()->update('letter_issue', ['deleted_on' => self::NOW], ['id' => $deletedIssueId]);
        $this->linkToDo($deletedIssueVersion, $todoVersionId);

        [, [$oldIssueVersion]] = $this->issue(2);
        $this->linkToDo($oldIssueVersion, $todoVersionId);

        $repo = $this->repo('LetterTodo');
        $this->assertSame([], $repo->fetchLiveIssueKeysUsing($id));
        $this->assertTrue($repo->isReferenced($id));
    }

    public function testToDoOnAGeneratedLetterIsReferenced(): void
    {
        [$id, [$old]] = $this->todo(2);
        [, [$issueVersionId]] = $this->issue();
        $instanceId = $this->letterInstance();

        $this->insert('letter_instance_todo', [
            'letter_instance_id' => $instanceId,
            'letter_instance_issue_id' => $this->instanceIssue($instanceId, $issueVersionId),
            'letter_todo_version_id' => $old,
            'display_order' => 1,
        ]);

        $repo = $this->repo('LetterTodo');
        $this->assertSame([], $repo->fetchLiveIssueKeysUsing($id));
        $this->assertTrue($repo->isReferenced($id));
    }

    public function testUnusedSectionIsHardDeletedIncludingDeletedVariants(): void
    {
        [$id, $variantIds] = $this->section();
        $repo = $this->repo('LetterSection');

        $this->assertSame([], $repo->fetchLetterTypeNamesUsing($id));
        $this->assertFalse($repo->isUsedByLetterInstances($id));

        $repo->hardDelete($id);

        $this->assertSame(0, $this->rows('letter_section', 'id', $id));
        $this->assertSame(0, $this->rows('letter_section_variant', 'letter_section_id', $id));
        foreach ($variantIds as $variantId) {
            $this->assertSame(0, $this->rows('letter_section_version', 'letter_section_variant_id', $variantId));
        }
    }

    public function testSectionOnALetterTypeNamesTheLetterTypes(): void
    {
        [$id] = $this->section();

        $this->insert('letter_type_section', [
            'letter_type_id' => $this->letterType('ITEST 7494 Alpha'),
            'letter_section_id' => $id,
            'display_order' => 1,
        ]);

        $this->assertSame(['ITEST 7494 Alpha'], $this->repo('LetterSection')->fetchLetterTypeNamesUsing($id));
    }

    public function testSectionOnAGeneratedLetterThroughADeletedVariantIsUsed(): void
    {
        [$id, , [, $deletedVariantVersion]] = $this->section();

        $this->insert('letter_instance_section', [
            'letter_instance_id' => $this->letterInstance(),
            'letter_section_version_id' => $deletedVariantVersion,
            'display_order' => 1,
        ]);

        $this->assertTrue($this->repo('LetterSection')->isUsedByLetterInstances($id));
    }

    public static function softDeleteProvider(): \Iterator
    {
        yield 'appendix' => ['LetterAppendix', 'letter_appendix', 'appendix'];
        yield 'issue' => ['LetterIssue', 'letter_issue', 'issue'];
        yield 'to-do' => ['LetterTodo', 'letter_todo', 'todo'];
        yield 'section' => ['LetterSection', 'letter_section', 'section'];
    }

    #[DataProvider('softDeleteProvider')]
    public function testSoftDeleteMarksTheRowAndAddsNoVersion(string $repoName, string $table, string $fixture): void
    {
        [$id] = $this->{$fixture}();
        $versionsBefore = $this->versionCount($table, $id);
        $repo = $this->repo($repoName);

        $repo->softDelete($repo->fetchById($id));

        $this->assertNotNull($this->conn()->fetchOne("SELECT deleted_on FROM {$table} WHERE id = ?", [$id]));
        $this->assertSame($versionsBefore, $this->versionCount($table, $id));
    }

    #[DataProvider('softDeleteProvider')]
    public function testListsLeaveOutSoftDeletedRows(string $repoName, string $table, string $fixture): void
    {
        [$liveId] = $this->{$fixture}();
        [$deletedId] = $this->{$fixture}();
        $this->conn()->update($table, ['deleted_on' => self::NOW], ['id' => $deletedId]);

        $dto = 'Dvsa\\Olcs\\Transfer\\Query\\Letter\\' . $repoName . '\\GetList';
        $query = $dto::create(['sort' => 'id', 'order' => 'DESC', 'page' => 1, 'limit' => 100]);
        $rows = iterator_to_array($this->repo($repoName)->fetchList($query, Query::HYDRATE_ARRAY));
        $ids = array_map(fn (array $row): int => (int) $row['id'], $rows);

        $this->assertContains($liveId, $ids);
        $this->assertNotContains($deletedId, $ids);
    }

    /**
     * Duplicate default variants can exist, and a versioned save would then compare the section
     * against the wrong variant and write a new version.
     */
    public function testSoftDeleteOfASectionWithDuplicateDefaultVariantsAddsNoVersion(): void
    {
        $id = $this->create('letter_section', ['section_key' => $this->key()]);

        foreach (['First', 'Second'] as $order => $name) {
            $variantId = $this->create('letter_section_variant', ['letter_section_id' => $id, 'display_order' => $order]);
            $versionId = $this->create('letter_section_version', [
                'letter_section_variant_id' => $variantId,
                'name' => $name,
                'section_type' => 'letter_section_type_body',
                'version_number' => 1,
                'created_on' => self::NOW,
            ]);
            $this->conn()->update('letter_section_variant', ['current_version_id' => $versionId], ['id' => $variantId]);
        }

        $this->conn()->update('letter_section', ['current_version_id' => $versionId], ['id' => $id]);
        $repo = $this->repo('LetterSection');

        $repo->softDelete($repo->fetchById($id));

        $this->assertSame(2, $this->versionCount('letter_section', $id));
    }

    private function conn(): Connection
    {
        return $this->em()->getConnection();
    }

    private function insert(string $table, array $row): void
    {
        $this->conn()->insert($table, $row);
    }

    private function create(string $table, array $row): int
    {
        $this->insert($table, $row);

        return (int) $this->conn()->lastInsertId();
    }

    private function rows(string $table, string $column, int $value): int
    {
        return (int) $this->conn()->fetchOne("SELECT COUNT(*) FROM {$table} WHERE {$column} = ?", [$value]);
    }

    private function versionCount(string $table, int $id): int
    {
        if ($table === 'letter_section') {
            return (int) $this->conn()->fetchOne(
                'SELECT COUNT(*) FROM letter_section_version sv'
                . ' JOIN letter_section_variant v ON v.id = sv.letter_section_variant_id'
                . ' WHERE v.letter_section_id = ?',
                [$id],
            );
        }

        return $this->rows($table . '_version', $table . '_id', $id);
    }

    private function key(string $label = 'X'): string
    {
        return 'itest-7494-' . $label . '-' . uniqid();
    }

    /**
     * @return array{int, list<int>} the parent id and its version ids, oldest first
     */
    private function versioned(string $table, array $row, array $versionRow, int $versions): array
    {
        $id = $this->create($table, $row);
        $versionIds = [];

        for ($number = 1; $number <= $versions; $number++) {
            $versionIds[] = $this->create($table . '_version', $versionRow + [
                $table . '_id' => $id,
                'version_number' => $number,
                'created_on' => self::NOW,
            ]);
        }

        $this->conn()->update($table, ['current_version_id' => end($versionIds)], ['id' => $id]);

        return [$id, $versionIds];
    }

    private function appendix(int $versions = 1): array
    {
        return $this->versioned('letter_appendix', ['appendix_key' => $this->key()], ['name' => 'Appendix'], $versions);
    }

    private function issue(int $versions = 1, ?string $key = null): array
    {
        $categoryId = $this->conn()->fetchOne('SELECT MIN(id) FROM category');

        return $this->versioned(
            'letter_issue',
            ['issue_key' => $key ?? $this->key()],
            ['heading' => 'Issue', 'category_id' => $categoryId],
            $versions,
        );
    }

    private function todo(int $versions = 1): array
    {
        return $this->versioned('letter_todo', ['todo_key' => $this->key()], [], $versions);
    }

    /**
     * A section with a default variant and a soft-deleted one, each with a version.
     *
     * @return array{int, list<int>, list<int>} section id, variant ids, version ids
     */
    private function section(): array
    {
        $id = $this->create('letter_section', ['section_key' => $this->key()]);
        $variantIds = [];
        $versionIds = [];

        foreach ([[null, null], [1, self::NOW]] as $order => [$isNi, $deletedDate]) {
            $variantId = $this->create('letter_section_variant', [
                'letter_section_id' => $id,
                'is_ni' => $isNi,
                'display_order' => $order,
                'deleted_date' => $deletedDate,
            ]);
            $versionId = $this->create('letter_section_version', [
                'letter_section_variant_id' => $variantId,
                'name' => 'Section',
                'section_type' => 'letter_section_type_body',
                'version_number' => 1,
                'created_on' => self::NOW,
            ]);
            $this->conn()->update('letter_section_variant', ['current_version_id' => $versionId], ['id' => $variantId]);

            $variantIds[] = $variantId;
            $versionIds[] = $versionId;
        }

        $this->conn()->update('letter_section', ['current_version_id' => $versionIds[0]], ['id' => $id]);

        return [$id, $variantIds, $versionIds];
    }

    private function linkToDo(int $issueVersionId, int $todoVersionId): void
    {
        $this->insert('letter_issue_todo', [
            'letter_issue_version_id' => $issueVersionId,
            'letter_todo_version_id' => $todoVersionId,
            'display_order' => 1,
        ]);
    }

    private function letterType(string $name): int
    {
        return $this->create('letter_type', ['name' => $name]);
    }

    private function letterInstance(): int
    {
        $licence = $this->conn()->fetchAssociative(
            'SELECT id, organisation_id FROM licence WHERE organisation_id IS NOT NULL ORDER BY id LIMIT 1'
        );
        $this->assertIsArray($licence, 'Expected the seeded test dataset to contain a licence');

        return $this->create('letter_instance', [
            'reference' => substr('IT' . uniqid(), 0, 20),
            'letter_type_id' => $this->letterType('ITEST 7494 Generated'),
            'licence_id' => $licence['id'],
            'organisation_id' => $licence['organisation_id'],
            'status' => 'ltr_sts_draft',
        ]);
    }

    private function instanceIssue(int $instanceId, int $issueVersionId): int
    {
        return $this->create('letter_instance_issue', [
            'letter_instance_id' => $instanceId,
            'letter_issue_version_id' => $issueVersionId,
            'display_order' => 1,
        ]);
    }
}
