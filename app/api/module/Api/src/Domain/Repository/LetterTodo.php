<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Letter\LetterTodo as Entity;
use Dvsa\Olcs\Api\Entity\Letter\LetterTodoVersion;

/**
 * LetterTodo Repository
 */
class LetterTodo extends AbstractVersionedRepository
{
    protected $entity = Entity::class;

    /**
     * Keys of the live issues whose current version links any version of the to-do
     *
     * @param int $id
     * @return string[]
     */
    public function fetchLiveIssueKeysUsing(int $id): array
    {
        return $this->getEntityManager()->getConnection()->fetchFirstColumn(
            'SELECT DISTINCT i.issue_key FROM letter_issue i ' .
            'JOIN letter_issue_todo lit ON lit.letter_issue_version_id = i.current_version_id ' .
            'JOIN letter_todo_version v ON v.id = lit.letter_todo_version_id ' .
            'WHERE v.letter_todo_id = :id AND i.deleted_on IS NULL ' .
            'ORDER BY i.issue_key',
            ['id' => $id]
        );
    }

    /**
     * Whether a generated letter or any issue version, old or deleted, points at the to-do
     *
     * @param int $id
     * @return bool
     */
    public function isReferenced(int $id): bool
    {
        return $this->getEntityManager()->getConnection()->fetchOne(
            'SELECT 1 FROM letter_todo_version v ' .
            'WHERE v.letter_todo_id = :id AND (' .
            'EXISTS (SELECT 1 FROM letter_instance_todo it WHERE it.letter_todo_version_id = v.id) ' .
            'OR EXISTS (SELECT 1 FROM letter_issue_todo lit WHERE lit.letter_todo_version_id = v.id)' .
            ') LIMIT 1',
            ['id' => $id]
        ) !== false;
    }

    /**
     * Delete the to-do and all its versions
     *
     * @param int $id
     * @return void
     */
    public function hardDelete(int $id): void
    {
        $this->deleteRows($id, [
            'UPDATE letter_todo SET current_version_id = NULL WHERE id = :id',
            'DELETE FROM letter_todo_version WHERE letter_todo_id = :id',
            'DELETE FROM letter_todo WHERE id = :id',
        ]);
    }

    /**
     * Get the list of fields that should trigger versioning when changed
     *
     * @return array
     */
    #[\Override]
    protected function getVersionedFields(): array
    {
        return [
            'name',
            'description',
            'helpText',
            'requiresInput'
        ];
    }

    /**
     * Get the version entity class name
     *
     * @return string
     */
    #[\Override]
    protected function getVersionEntityClass(): string
    {
        return LetterTodoVersion::class;
    }

    /**
     * Get the parent entity short name (for setter method)
     *
     * @return string
     */
    #[\Override]
    protected function getEntityShortName(): string
    {
        return 'LetterTodo';
    }
}
