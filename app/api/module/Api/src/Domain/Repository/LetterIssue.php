<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Letter\LetterIssue as Entity;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion;

/**
 * LetterIssue Repository
 */
class LetterIssue extends AbstractVersionedRepository
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
            'category',
            'subCategory',
            'heading',
            'defaultBodyContent',
            'helpText',
            'minLength',
            'maxLength',
            'requiresInput',
            'isNi',
            'goodsOrPsv'
        ];
    }

    /**
     * Get the version entity class name
     *
     * @return string
     */
    protected function getVersionEntityClass(): string
    {
        return LetterIssueVersion::class;
    }

    /**
     * Get the parent entity short name (for setter method)
     *
     * @return string
     */
    protected function getEntityShortName(): string
    {
        return 'LetterIssue';
    }
}