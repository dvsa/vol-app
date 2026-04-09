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
