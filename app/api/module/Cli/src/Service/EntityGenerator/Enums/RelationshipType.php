<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\EntityGenerator\Enums;

/**
 * Doctrine relationship types enum
 */
enum RelationshipType: string
{
    case MANY_TO_ONE = 'manyToOne';
    case ONE_TO_MANY = 'oneToMany';
    case MANY_TO_MANY = 'manyToMany';
    case ONE_TO_ONE = 'oneToOne';

    /**
     * Get the inverse relationship type
     */
    public function getInverse(): self
    {
        return match ($this) {
            self::MANY_TO_ONE => self::ONE_TO_MANY,
            self::ONE_TO_MANY => self::MANY_TO_ONE,
            self::MANY_TO_MANY => self::MANY_TO_MANY,
            self::ONE_TO_ONE => self::ONE_TO_ONE,
        };
    }

    /**
     * Check if this relationship creates a collection
     */
    public function isCollection(): bool
    {
        return match ($this) {
            self::ONE_TO_MANY, self::MANY_TO_MANY => true,
            self::MANY_TO_ONE, self::ONE_TO_ONE => false,
        };
    }

    /**
     * Get the property suffix for collections
     */
    public function getPropertySuffix(): string
    {
        return $this->isCollection() ? 's' : '';
    }
}