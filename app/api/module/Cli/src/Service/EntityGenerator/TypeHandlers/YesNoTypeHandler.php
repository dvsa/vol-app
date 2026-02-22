<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\EntityGenerator\TypeHandlers;

use Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces\ColumnMetadata;
use Dvsa\Olcs\Cli\Service\EntityGenerator\ValueObjects\FieldConfig;
use Dvsa\Olcs\Cli\Service\EntityGenerator\Enums\CustomFieldType;

/**
 * Type handler for YesNo custom type
 */
class YesNoTypeHandler extends AbstractTypeHandler
{
    public function supports(ColumnMetadata $column, array $config = []): bool
    {
        // Check if a FieldConfig object was passed in config array
        if (isset($config['fieldConfig']) && $config['fieldConfig'] instanceof FieldConfig) {
            $fieldConfig = $config['fieldConfig'];
            return $fieldConfig->type === CustomFieldType::YESNO
                || $fieldConfig->type === CustomFieldType::YESNO_NULL;
        }

        // Legacy support: Check if config is passed as array with column name as key
        $columnConfig = $config[$column->getName()] ?? null;

        if ($columnConfig !== null) {
            // Handle both FieldConfig object and array formats
            if ($columnConfig instanceof FieldConfig) {
                return $columnConfig->type === CustomFieldType::YESNO
                    || $columnConfig->type === CustomFieldType::YESNO_NULL;
            } elseif (is_array($columnConfig) && isset($columnConfig['type'])) {
                $type = $columnConfig['type'];
                return $type === 'yesno' || $type === 'yesnonull';
            }
        }

        // Check if column has doctrine type hint in comment
        $doctrineType = $column->getDoctrineType();
        return $doctrineType === 'yesno' || $doctrineType === 'yesnonull';
    }

    /**
     * Enhanced supports method with FieldConfig
     */
    public function supportsWithConfig(ColumnMetadata $column, FieldConfig|null $fieldConfig = null): bool
    {
        if ($fieldConfig?->type !== null) {
            return $fieldConfig->type === CustomFieldType::YESNO
                || $fieldConfig->type === CustomFieldType::YESNO_NULL;
        }

        return $this->supports($column);
    }

    public function generateAnnotation(ColumnMetadata $column, array $config = []): string
    {
        // Determine if this is yesno or yesnonull
        $fieldConfig = isset($config[$column->getName()])
            ? FieldConfig::fromArray($config[$column->getName()])
            : null;

        $doctrineType = $this->getDoctrineType($column, $fieldConfig);

        $options = [];

        // Add nullable option
        if ($column->isNullable() || $doctrineType === 'yesnonull') {
            $options[] = 'nullable=true';
        } else {
            $options[] = 'nullable=false';
        }

        // Add default value option
        if ($column->getDefault() !== null) {
            // For yesno fields, the default is always numeric (0 or 1)
            $default = $column->getDefault();
            $defaultValue = is_numeric($default) ? (string)$default : '0';
            $options[] = 'options={"default": ' . $defaultValue . '}';
        }

        $optionsStr = !empty($options) ? ', ' . implode(', ', $options) : '';

        return sprintf(
            '@ORM\Column(type="%s", name="%s"%s)',
            $doctrineType,
            $column->getName(),
            $optionsStr
        );
    }

    /**
     * Enhanced annotation generation with FieldConfig
     */
    public function generateAnnotationWithConfig(ColumnMetadata $column, FieldConfig|null $fieldConfig = null): string
    {
        $doctrineType = $fieldConfig?->type?->getDoctrineType() ?? 'yesno';

        $options = [];

        // Add nullable option
        $isNullable = $column->isNullable() || $fieldConfig?->type === CustomFieldType::YESNO_NULL;
        $options[] = $isNullable ? 'nullable=true' : 'nullable=false';

        // Add default value option
        if ($column->getDefault() !== null) {
            // For yesno fields, the default is always numeric (0 or 1)
            $default = $column->getDefault();
            $defaultValue = is_numeric($default) ? (string)$default : '0';
            $options[] = 'options={"default": ' . $defaultValue . '}';
        }

        $optionsStr = !empty($options) ? ', ' . implode(', ', $options) : '';

        return sprintf(
            '@ORM\Column(type="%s", name="%s"%s)',
            $doctrineType,
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

        // YesNo type typically stores Y/N but we want to initialize with 0/1
        // Get the raw default value from the database
        $rawDefault = $column->getDefault();

        // Generate the properly formatted default value for PHP code
        if ($rawDefault !== null) {
            // Database stores 0 or 1, so we just use that numeric value directly
            $defaultValue = is_numeric($rawDefault) ? (string)$rawDefault : $this->generateDefaultValue($rawDefault);
        } else {
            $defaultValue = $column->isNullable() ? 'null' : '0';
        }

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

    /**
     * Get the effective Doctrine type for this column
     */
    private function getDoctrineType(ColumnMetadata $column, FieldConfig|null $fieldConfig = null): string
    {
        if ($fieldConfig?->type !== null) {
            return $fieldConfig->type->getDoctrineType();
        }

        $doctrineType = $column->getDoctrineType();
        if ($doctrineType === 'yesno' || $doctrineType === 'yesnonull') {
            return $doctrineType;
        }

        // Default to yesno if we're handling this field
        return 'yesno';
    }
}
