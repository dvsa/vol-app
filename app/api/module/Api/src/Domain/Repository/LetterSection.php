<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Letter\LetterSection as Entity;
use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion;

/**
 * LetterSection Repository
 */
class LetterSection extends AbstractVersionedRepository
{
    protected $entity = Entity::class;

    /**
     * Get the list of fields that should trigger versioning when changed
     *
     * @return array
     */
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
            'isNi',
            'publishFrom'
        ];
    }

    /**
     * Get the version entity class name
     *
     * @return string
     */
    protected function getVersionEntityClass(): string
    {
        return LetterSectionVersion::class;
    }

    /**
     * Get the parent entity short name (for setter method)
     *
     * @return string
     */
    protected function getEntityShortName(): string
    {
        return 'LetterSection';
    }
}