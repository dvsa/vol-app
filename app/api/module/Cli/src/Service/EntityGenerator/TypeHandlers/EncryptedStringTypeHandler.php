<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\EntityGenerator\TypeHandlers;

use Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces\ColumnMetadata;

/**
 * Type handler for encrypted string custom type
 */
class EncryptedStringTypeHandler extends AbstractTypeHandler
{
    public function supports(ColumnMetadata $column, array $config = []): bool
    {
        // Check if a FieldConfig object was passed in config array
        if (isset($config['fieldConfig']) && $config['fieldConfig'] instanceof \Dvsa\Olcs\Cli\Service\EntityGenerator\ValueObjects\FieldConfig) {
            $fieldConfig = $config['fieldConfig'];
            return $fieldConfig->type !== null && $fieldConfig->type->value === 'encrypted_string';
        }

        // Legacy support: Check if config is passed as array with column name as key
        $columnConfig = $config[$column->getName()] ?? null;

        if ($columnConfig !== null) {
            // Handle both FieldConfig object and array formats
            if (is_object($columnConfig) && method_exists($columnConfig, 'type') && $columnConfig->type !== null) {
                return $columnConfig->type->value === 'encrypted_string';
            } elseif (is_array($columnConfig) && isset($columnConfig['type'])) {
                return $columnConfig['type'] === 'encrypted_string';
            }
        }

        // Check if column has doctrine type hint in comment
        return $column->getDoctrineType() === 'encrypted_string';
    }

    public function generateAnnotation(ColumnMetadata $column, array $config = []): string
    {
        $options = [];

        // Add nullable option
        if ($column->isNullable()) {
            $options[] = 'nullable=true';
        } else {
            $options[] = 'nullable=false';
        }

        // Add length if specified
        if ($column->getLength() !== null) {
            $options[] = 'length=' . $column->getLength();
        }

        $optionsStr = !empty($options) ? ', ' . implode(', ', $options) : '';

        return sprintf(
            '@ORM\Column(type="encrypted_string", name="%s"%s)',
            $column->getName(),
            $optionsStr
        );
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
        $comment = $this->generateComment($column, $config);

        $defaultValue = $column->getDefault() !== null
            ? $this->generateDefaultValue($column->getDefault())
            : ($column->isNullable() ? 'null' : "''");

        return [
            'name' => $propertyName,
            'type' => 'string',
            'docBlock' => $comment,
            'defaultValue' => $defaultValue,
            'nullable' => $column->isNullable(),
            'isRelationship' => false,
        ];
    }

    #[\Override]
    public function getPriority(): int
    {
        return 100; // High priority for custom types
    }

    /**
     * Generate property comment
     */
    private function generateComment(ColumnMetadata $column, array $config): string
    {
        $comment = $column->getComment() ?: $this->generatePropertyName($column->getName());

        // Clean up the comment (remove doctrine type hints)
        $comment = preg_replace('/\s*\(DC2Type:[^)]+\)\s*/', '', $comment);
        $comment = trim((string) $comment);

        if (empty($comment)) {
            $comment = ucfirst(str_replace('_', ' ', $column->getName()));
        }

        return $comment;
    }
}
