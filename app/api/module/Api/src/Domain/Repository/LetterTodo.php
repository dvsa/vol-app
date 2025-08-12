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
     * Get the list of fields that should trigger versioning when changed
     *
     * @return array
     */
    protected function getVersionedFields(): array
    {
        return [
            'description',
            'helpText'
        ];
    }

    /**
     * Get the version entity class name
     *
     * @return string
     */
    protected function getVersionEntityClass(): string
    {
        return LetterTodoVersion::class;
    }

    /**
     * Get the parent entity short name (for setter method)
     *
     * @return string
     */
    protected function getEntityShortName(): string
    {
        return 'LetterTodo';
    }
}