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
     * Get the list of fields that should trigger versioning when changed
     *
     * @return array
     */
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
    protected function getVersionEntityClass(): string
    {
        return LetterAppendixVersion::class;
    }

    /**
     * Get the parent entity short name (for setter method)
     *
     * @return string
     */
    protected function getEntityShortName(): string
    {
        return 'LetterAppendix';
    }
}
