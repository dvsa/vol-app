<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Letter\LetterAppendix as Entity;
use Dvsa\Olcs\Api\Entity\Letter\LetterAppendixVersion;

/**
 * LetterAppendix Repository
 */
class LetterAppendix extends AbstractVersionedRepository
{
    protected $entity = Entity::class;

    /**
     * Names of the letter types using any version of the appendix
     *
     * @param int $id
     * @return string[]
     */
    public function fetchLetterTypeNamesUsing(int $id): array
    {
        return $this->getEntityManager()->getConnection()->fetchFirstColumn(
            'SELECT DISTINCT lt.name FROM letter_type lt ' .
            'JOIN letter_type_appendix lta ON lta.letter_type_id = lt.id ' .
            'JOIN letter_appendix_version v ON v.id = lta.letter_appendix_version_id ' .
            'WHERE v.letter_appendix_id = :id ' .
            'ORDER BY lt.name',
            ['id' => $id]
        );
    }

    /**
     * Whether any generated letter uses a version of the appendix
     *
     * @param int $id
     * @return bool
     */
    public function isUsedByLetterInstances(int $id): bool
    {
        return $this->getEntityManager()->getConnection()->fetchOne(
            'SELECT 1 FROM letter_instance_appendix lia ' .
            'JOIN letter_appendix_version v ON v.id = lia.letter_appendix_version_id ' .
            'WHERE v.letter_appendix_id = :id LIMIT 1',
            ['id' => $id]
        ) !== false;
    }

    /**
     * Delete the appendix and all its versions
     *
     * @param int $id
     * @return void
     */
    public function hardDelete(int $id): void
    {
        $this->deleteRows($id, [
            'UPDATE letter_appendix SET current_version_id = NULL WHERE id = :id',
            'DELETE FROM letter_appendix_version WHERE letter_appendix_id = :id',
            'DELETE FROM letter_appendix WHERE id = :id',
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
            'document',
            'appendixType',
            'defaultContent',
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
        return LetterAppendixVersion::class;
    }

    /**
     * Get the parent entity short name (for setter method)
     *
     * @return string
     */
    #[\Override]
    protected function getEntityShortName(): string
    {
        return 'LetterAppendix';
    }
}
