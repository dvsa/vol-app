<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\EntityGenerator\TypeHandlers;

use Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces\ColumnMetadata;
use Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces\TypeHandlerInterface;

/**
 * Abstract base class for type handlers
 */
abstract class AbstractTypeHandler implements TypeHandlerInterface
{
    public function getPriority(): int
    {
        return 0; // Default priority
    }

    public function getRequiredImports(): array
    {
        return []; // Default: no imports required
    }

    /**
     * Generate property name from column name (snake_case to camelCase)
     */
    protected function generatePropertyName(string $columnName): string
    {
        // Remove _id suffix if present for relationships
        $propertyName = preg_replace('/_id$/', '', $columnName);
        
        // Convert to camelCase
        return lcfirst(str_replace('_', '', ucwords($propertyName, '_')));
    }

    /**
     * Generate getter method name
     */
    protected function generateGetterName(string $propertyName): string
    {
        return 'get' . ucfirst($propertyName);
    }

    /**
     * Generate setter method name
     */
    protected function generateSetterName(string $propertyName): string
    {
        return 'set' . ucfirst($propertyName);
    }

    /**
     * Escape string for PHP code generation
     */
    protected function escapeString(?string $value): string
    {
        if ($value === null) {
            return 'null';
        }

        return "'" . addslashes($value) . "'";
    }

    /**
     * Generate default value representation
     */
    protected function generateDefaultValue(mixed $default): string
    {
        if ($default === null) {
            return 'null';
        }

        // Check for numeric values FIRST (before string check)
        // This handles Doctrine DBAL returning '1' as string instead of int
        if (is_numeric($default)) {
            return (string) $default;
        }

        if (is_bool($default)) {
            return $default ? 'true' : 'false';
        }

        if (is_string($default)) {
            return $this->escapeString($default);
        }

        return $this->escapeString((string) $default);
    }
}