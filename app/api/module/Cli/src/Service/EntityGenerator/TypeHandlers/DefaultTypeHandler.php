<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\EntityGenerator\TypeHandlers;

use Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces\ColumnMetadata;

/**
 * Default type handler for standard database types
 */
class DefaultTypeHandler extends AbstractTypeHandler
{
    private const array TYPE_MAPPING = [
        'bigint' => ['int', 'integer'],
        'binary' => ['string', 'string'],
        'blob' => ['string', 'string'],
        'boolean' => ['bool', 'boolean'],
        'date' => ['\\DateTime', 'date'],
        'datetime' => ['\\DateTime', 'datetime'],
        'decimal' => ['string', 'decimal'],
        'float' => ['float', 'float'],
        'integer' => ['int', 'integer'],
        'json' => ['array', 'json'],
        'smallint' => ['int', 'smallint'],
        'string' => ['string', 'string'],
        'text' => ['string', 'text'],
        'time' => ['\\DateTime', 'time'],
        'timestamp' => ['\\DateTime', 'datetime'],
        'tinyint' => ['int', 'boolean'], // tinyint(1) is typically boolean
    ];

    public function supports(ColumnMetadata $column, array $config = []): bool
    {
        // This is the fallback handler, so it supports everything
        return true;
    }

    public function generateAnnotation(ColumnMetadata $column, array $config = []): string
    {
        $type = $this->getDoctrineType($column, $config);
        $options = [];
        $annotations = [];

        // Add type
        $options[] = 'type="' . $type . '"';

        // Add name
        $options[] = 'name="' . $column->getName() . '"';

        // Add length for string types
        if (in_array($type, ['string']) && $column->getLength() !== null) {
            $options[] = 'length=' . $column->getLength();
        }

        // Add nullable
        if ($column->isNullable()) {
            $options[] = 'nullable=true';
        } else {
            $options[] = 'nullable=false';
        }

        // Add default value as option
        if ($column->getDefault() !== null) {
            // Convert string default values to proper types for boolean columns
            $default = $column->getDefault();
            $doctrineType = $this->getDoctrineType($column, $config);

            if ($doctrineType === 'boolean' && is_string($default)) {
                // For boolean columns, use numeric values (0 or 1) for compatibility
                $defaultValue = ($default === '1' || $default === 'true') ? '1' : '0';
            } elseif (in_array($doctrineType, ['integer', 'smallint', 'bigint', 'decimal', 'float']) && is_numeric($default)) {
                // For numeric types, don't quote the default value in options
                $defaultValue = (string) $default;
            } elseif (is_string($default)) {
                // For string types in options array, use double quotes escaped for PHP
                $defaultValue = '"' . addslashes($default) . '"';
            } else {
                $defaultValue = $this->generateDefaultValue($default);
            }

            $options[] = 'options={"default": ' . $defaultValue . '}';
        }

        // Add precision and scale for decimal types
        if ($type === 'decimal' && $column->getLength() !== null) {
            $options[] = 'precision=' . $column->getLength();
            if ($column->getOption('scale') !== null) {
                $options[] = 'scale=' . $column->getOption('scale');
            }
        }

        // Build the column annotation
        $annotations[] = '@ORM\Column(' . implode(', ', $options) . ')';

        // Check if field is translatable from EntityConfig
        $columnConfig = $config[$column->getName()] ?? null;
        if ($columnConfig instanceof \Dvsa\Olcs\Cli\Service\EntityGenerator\ValueObjects\FieldConfig && $columnConfig->translatable) {
            $annotations[] = '@Gedmo\Translatable';
        } elseif (is_array($columnConfig) && ($columnConfig['translatable'] ?? false)) {
            $annotations[] = '@Gedmo\Translatable';
        }

        return implode("\n     * ", $annotations);
    }

    /**
     * Override to not remove _id suffix for regular columns
     */
    #[\Override]
    protected function generatePropertyName(string $columnName): string
    {
        // For regular columns, just convert to camelCase without removing _id
        return lcfirst(str_replace('_', '', ucwords($columnName, '_')));
    }

    public function generateProperty(ColumnMetadata $column, array $config = []): array
    {
        $propertyName = $this->generatePropertyName($column->getName());
        $phpType = $this->getPhpType($column, $config);
        $comment = $this->generateComment($column, $config);

        $defaultValue = $this->generatePropertyDefault($column, $config);

        return [
            'name' => $propertyName,
            'type' => $phpType,
            'docBlock' => $comment,
            'defaultValue' => $defaultValue,
            'nullable' => $column->isNullable(),
            'isRelationship' => false,
        ];
    }

    #[\Override]
    public function getPriority(): int
    {
        return -100; // Lowest priority (fallback)
    }

    #[\Override]
    public function getRequiredImports(): array
    {
        return ['Doctrine\ORM\Mapping as ORM'];
    }

    /**
     * Get Doctrine type for the column
     */
    private function getDoctrineType(ColumnMetadata $column, array $config = []): string
    {
        $dbType = strtolower($column->getType());

        // Check if FieldConfig has an explicit override
        $fieldConfig = $config['fieldConfig'] ?? ($config[$column->getName()] ?? null);
        if ($fieldConfig instanceof \Dvsa\Olcs\Cli\Service\EntityGenerator\ValueObjects\FieldConfig && $fieldConfig->type !== null) {
            // Use the explicit override from EntityConfig
            return $fieldConfig->type->getDoctrineType();
        }

        // Handle tinyint(1) as boolean (only if not overridden)
        if ($dbType === 'tinyint' && $column->getLength() === 1) {
            return 'boolean';
        }

        return self::TYPE_MAPPING[$dbType][1] ?? 'string';
    }

    /**
     * Get PHP type for the column
     */
    private function getPhpType(ColumnMetadata $column, array $config = []): string
    {
        $dbType = strtolower($column->getType());

        // Check if FieldConfig has an explicit override
        $fieldConfig = $config['fieldConfig'] ?? ($config[$column->getName()] ?? null);
        if ($fieldConfig instanceof \Dvsa\Olcs\Cli\Service\EntityGenerator\ValueObjects\FieldConfig && $fieldConfig->type !== null) {
            // Use the explicit override from EntityConfig
            return $fieldConfig->type->getPhpType();
        }

        // Handle tinyint(1) as boolean (only if not overridden)
        if ($dbType === 'tinyint' && $column->getLength() === 1) {
            return 'bool';
        }

        $phpType = self::TYPE_MAPPING[$dbType][0] ?? 'string';

        // Make nullable types nullable in PHP 8+
        if ($column->isNullable() && $phpType !== 'mixed') {
            return $phpType;
        }

        return $phpType;
    }

    /**
     * Generate property default value
     */
    private function generatePropertyDefault(ColumnMetadata $column, array $config = []): string
    {
        if ($column->getDefault() !== null) {
            // Convert string default values to proper types for boolean columns
            $default = $column->getDefault();
            $phpType = $this->getPhpType($column, $config);
            $doctrineType = $this->getDoctrineType($column, $config);

            if (($phpType === 'bool' || $doctrineType === 'boolean') && is_string($default)) {
                // For boolean columns, use numeric values (0 or 1) for compatibility
                return ($default === '1' || $default === 'true') ? '1' : '0';
            }

            return $this->generateDefaultValue($default);
        }

        if ($column->isNullable()) {
            return 'null';
        }

        // Generate sensible defaults for non-nullable fields
        $phpType = $this->getPhpType($column, $config);

        return match ($phpType) {
            'int' => '0',
            'float' => '0.0',
            'bool' => '0',  // Use numeric 0 for compatibility
            'string' => "''",
            'array' => '[]',
            default => 'null'
        };
    }

    /**
     * Generate property comment
     */
    private function generateComment(ColumnMetadata $column, array $config): string
    {
        $comment = $column->getComment();

        if (empty($comment)) {
            $comment = ucfirst(str_replace('_', ' ', $column->getName()));
        }

        return $comment;
    }
}
