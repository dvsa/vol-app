<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Letter\LetterSection as Entity;
use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVariant;
use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion;

/**
 * LetterSection Repository
 */
class LetterSection extends AbstractVersionedRepository
{
    protected $entity = Entity::class;

    /**
     * Save with variant-aware versioning.
     *
     * When creating a new section, we need to create a default variant first,
     * then the version goes on the variant (not directly on the section).
     * For existing sections, edits create a new version on the default variant.
     */
    #[\Override]
    public function save($entity)
    {
        if (!($entity instanceof Entity)) {
            throw new \Dvsa\Olcs\Api\Domain\Exception\RuntimeException(
                'This repository can only save entities of type ' . Entity::class
            );
        }

        $defaultVariant = $entity->getDefaultVariant();
        $isNew = ($defaultVariant === null);

        if ($isNew) {
            // New section: create default variant (all NULL conditions)
            $defaultVariant = new LetterSectionVariant();
            $defaultVariant->setLetterSection($entity);
            $defaultVariant->setDisplayOrder(0);
            $entity->addVariant($defaultVariant);
        }

        // Check if we need a new version on the default variant
        $currentVersion = $defaultVariant->getCurrentVersion();
        $currentState = $this->extractEntityState($entity);

        $needsVersion = false;
        $versionNumber = 1;

        if (!$currentVersion) {
            $needsVersion = true;
        } else {
            if ($this->hasChanges($currentVersion, $currentState)) {
                $needsVersion = true;
                $versionNumber = $currentVersion->getVersionNumber() + 1;
            }
        }

        if ($needsVersion) {
            $newVersion = new LetterSectionVersion();
            $newVersion->setLetterSectionVariant($defaultVariant);

            foreach ($currentState as $field => $value) {
                $setter = 'set' . ucfirst((string) $field);
                if (method_exists($newVersion, $setter)) {
                    $newVersion->$setter($value);
                }
            }

            $newVersion->setVersionNumber($versionNumber);

            $defaultVariant->setCurrentVersion($newVersion);
            $entity->setCurrentVersion($newVersion);

            $this->getEntityManager()->persist($newVersion);
        }

        // Save via AbstractRepository (skip AbstractVersionedRepository)
        AbstractRepository::save($entity);
    }

    /**
     * Names of the letter types using the section
     *
     * @param int $id
     * @return string[]
     */
    public function fetchLetterTypeNamesUsing(int $id): array
    {
        return $this->getEntityManager()->getConnection()->fetchFirstColumn(
            'SELECT DISTINCT lt.name FROM letter_type lt ' .
            'JOIN letter_type_section lts ON lts.letter_type_id = lt.id ' .
            'WHERE lts.letter_section_id = :id ' .
            'ORDER BY lt.name',
            ['id' => $id]
        );
    }

    /**
     * Whether any generated letter uses a version of the section, from any variant including deleted ones
     *
     * @param int $id
     * @return bool
     */
    public function isUsedByLetterInstances(int $id): bool
    {
        return $this->getEntityManager()->getConnection()->fetchOne(
            'SELECT 1 FROM letter_instance_section lis ' .
            'JOIN letter_section_version sv ON sv.id = lis.letter_section_version_id ' .
            'JOIN letter_section_variant v ON v.id = sv.letter_section_variant_id ' .
            'WHERE v.letter_section_id = :id LIMIT 1',
            ['id' => $id]
        ) !== false;
    }

    /**
     * Delete the section with all its variants, including deleted ones, and their versions
     *
     * @param int $id
     * @return void
     */
    public function hardDelete(int $id): void
    {
        $this->deleteRows($id, [
            'UPDATE letter_section SET current_version_id = NULL WHERE id = :id',
            'UPDATE letter_section_variant SET current_version_id = NULL WHERE letter_section_id = :id',
            'DELETE sv FROM letter_section_version sv ' .
            'JOIN letter_section_variant v ON v.id = sv.letter_section_variant_id ' .
            'WHERE v.letter_section_id = :id',
            'DELETE FROM letter_section_variant WHERE letter_section_id = :id',
            'DELETE FROM letter_section WHERE id = :id',
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
            'defaultContent',
            'helpText',
            'requiresInput',
            'minLength',
            'maxLength',
            'sectionType',
            'goodsOrPsv',
            'isNi'
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
        return LetterSectionVersion::class;
    }

    /**
     * Get the parent entity short name (for setter method)
     *
     * @return string
     */
    #[\Override]
    protected function getEntityShortName(): string
    {
        return 'LetterSection';
    }
}
