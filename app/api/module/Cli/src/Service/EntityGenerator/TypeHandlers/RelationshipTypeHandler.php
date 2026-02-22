<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\EntityGenerator\TypeHandlers;

use Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces\ColumnMetadata;
use Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces\TableMetadata;

/**
 * Type handler for relationship columns (foreign keys)
 */
class RelationshipTypeHandler extends AbstractTypeHandler
{
    private ?TableMetadata $currentTable = null;

    /**
     * Set the current table being processed
     */
    public function setCurrentTable(?TableMetadata $table): void
    {
        $this->currentTable = $table;
    }

    public function supports(ColumnMetadata $column, array $config = []): bool
    {
        // Only handle columns that are actual foreign keys
        if ($this->currentTable === null) {
            return false;
        }

        $columnName = $column->getName();

        // Check if this column is part of a foreign key constraint
        foreach ($this->currentTable->getForeignKeys() as $foreignKey) {
            $localColumns = is_array($foreignKey) ? ($foreignKey['local_columns'] ?? []) : $foreignKey->getLocalColumns();
            if (in_array($columnName, $localColumns)) {
                return true;
            }
        }

        return false;
    }

    public function generateAnnotation(ColumnMetadata $column, array $config = []): string
    {
        $targetEntity = $this->getTargetEntity($column, $config);
        $referencedColumn = $this->getReferencedColumn($column, $config);

        // Determine if this should be OneToOne based on unique constraint
        $isOneToOne = $this->isOneToOneRelationship($column);
        $relationType = $isOneToOne ? 'OneToOne' : 'ManyToOne';

        $options = [
            'targetEntity="' . $targetEntity . '"',
            'fetch="LAZY"'
        ];

        $joinOptions = [
            'name="' . $column->getName() . '"',
            'referencedColumnName="' . $referencedColumn . '"'
        ];

        if ($column->isNullable()) {
            $joinOptions[] = 'nullable=true';
        }

        // Check for cascade options in config
        $cascadeOptions = $this->getCascadeOptions($column, $config);
        if (!empty($cascadeOptions)) {
            $options[] = 'cascade={' . implode(', ', array_map(fn($c) => '"' . $c . '"', $cascadeOptions)) . '}';
        }

        // Check for orphanRemoval
        $orphanRemoval = $this->getOrphanRemoval($column, $config);
        if ($orphanRemoval) {
            $options[] = 'orphanRemoval=true';
        }

        // Check for indexBy
        $indexBy = $this->getIndexBy($column, $config);
        if ($indexBy !== null) {
            $options[] = 'indexBy="' . $indexBy . '"';
        }

        $annotations = [sprintf(
            "@ORM\%s(%s)",
            $relationType,
            implode(', ', $options)
        )];

        $annotations[] = sprintf("@ORM\JoinColumn(%s)", implode(', ', $joinOptions));

        // Add OrderBy annotation if specified (only for collections)
        if ($relationType === 'OneToMany' || $relationType === 'ManyToMany') {
            $orderBy = $this->getOrderBy($column, $config);
            if (!empty($orderBy)) {
                $orderByPairs = [];
                foreach ($orderBy as $field => $direction) {
                    $orderByPairs[] = sprintf('"%s" = "%s"', $field, strtoupper((string) $direction));
                }
                $annotations[] = sprintf('@ORM\OrderBy({%s})', implode(', ', $orderByPairs));
            }
        }

        return implode("\n     * ", $annotations);
    }

    /**
     * Check if this column should be a OneToOne relationship
     */
    private function isOneToOneRelationship(ColumnMetadata $column): bool
    {
        if ($this->currentTable === null) {
            return false;
        }

        $columnName = $column->getName();

        // Check if this column is part of a unique constraint (excluding composite constraints)
        foreach ($this->currentTable->getUniqueConstraints() as $constraint) {
            $columns = $constraint['columns'] ?? [];
            if (count($columns) === 1 && in_array($columnName, $columns)) {
                return true;
            }
        }

        return false;
    }

    public function generateProperty(ColumnMetadata $column, array $config = []): array
    {
        $propertyName = $this->generatePropertyName($column->getName());
        $targetEntity = $this->getTargetEntity($column, $config);
        $comment = $this->generateComment($column, $config);

        return [
            'name' => $propertyName,
            'type' => '\\' . $targetEntity,
            'docBlock' => $comment,
            'defaultValue' => 'null',
            'nullable' => true, // Relationships are typically nullable for initialization
            'isRelationship' => true,
        ];
    }

    #[\Override]
    public function getPriority(): int
    {
        return 50; // Medium priority
    }

    /**
     * Get target entity class name
     */
    private function getTargetEntity(ColumnMetadata $column, array $config): string
    {
        $columnName = $column->getName();

        // Check if there's a custom mapping in config
        if (isset($config['targetEntity'][$columnName])) {
            return $config['targetEntity'][$columnName];
        }

        // Get the foreign table name from the foreign key constraint
        if ($this->currentTable !== null) {
            foreach ($this->currentTable->getForeignKeys() as $foreignKey) {
                $localColumns = is_array($foreignKey) ? ($foreignKey['local_columns'] ?? []) : $foreignKey->getLocalColumns();
                if (in_array($columnName, $localColumns)) {
                    // Get the foreign table name from the constraint
                    $foreignTableName = is_array($foreignKey) ? ($foreignKey['foreign_table'] ?? null) : $foreignKey->getForeignTableName();
                    if ($foreignTableName !== null) {
                        // Check for custom class name mapping first
                        $entityName = $config['mappingConfig']['classNameForTable'][$foreignTableName] ?? null;
                        if ($entityName === null) {
                            // Convert table name to entity name
                            $entityName = $this->tableNameToEntityName($foreignTableName);
                        }

                        // Check if there's a namespace mapping
                        $namespace = $this->getEntityNamespace($entityName, $config);

                        return $namespace . '\\' . $entityName;
                    }
                }
            }
        }

        // Try to derive from column name as fallback
        if (preg_match('/^(.+)_id$/', $columnName, $matches)) {
            $entityName = $this->columnNameToEntityName($matches[1]);

            // Check if there's a namespace mapping
            $namespace = $this->getEntityNamespace($entityName, $config);

            return $namespace . '\\' . $entityName;
        }

        // Fallback to RefData for unrecognized patterns
        return \Dvsa\Olcs\Api\Entity\System\RefData::class;
    }

    /**
     * Get referenced column name
     */
    private function getReferencedColumn(ColumnMetadata $column, array $config): string
    {
        // Default to 'id'
        return $config['referencedColumn'][$column->getName()] ?? 'id';
    }

    /**
     * Get cascade options from config
     */
    private function getCascadeOptions(ColumnMetadata $column, array $config): array
    {
        // First check if there's a fieldConfig key (new format)
        $columnConfig = $config['fieldConfig'] ?? null;

        // Check if it's a FieldConfig object
        if ($columnConfig instanceof \Dvsa\Olcs\Cli\Service\EntityGenerator\ValueObjects\FieldConfig) {
            return $columnConfig->cascade;
        }

        // Fallback to column name lookup (legacy format)
        $columnName = $column->getName();
        $columnConfig = $config[$columnName] ?? null;

        if ($columnConfig instanceof \Dvsa\Olcs\Cli\Service\EntityGenerator\ValueObjects\FieldConfig) {
            return $columnConfig->cascade;
        }

        // Check array format
        if (is_array($columnConfig) && isset($columnConfig['cascade'])) {
            return $columnConfig['cascade'];
        }

        return [];
    }

    /**
     * Convert column name to entity name
     */
    private function columnNameToEntityName(string $columnName): string
    {
        // Convert snake_case to PascalCase
        return str_replace('_', '', ucwords($columnName, '_'));
    }

    /**
     * Convert table name to entity name
     */
    private function tableNameToEntityName(string $tableName): string
    {
        // Convert snake_case to PascalCase
        return str_replace('_', '', ucwords($tableName, '_'));
    }

    /**
     * Get entity namespace based on entity name
     */
    private function getEntityNamespace(string $entityName, array $config): string
    {
        $namespaces = $config['namespaces'] ?? [];

        // The namespace config maps entity names to namespace folders
        if (isset($namespaces[$entityName])) {
            return 'Dvsa\\Olcs\\Api\\Entity\\' . $namespaces[$entityName];
        }

        // Default namespace (root Entity folder)
        return 'Dvsa\\Olcs\\Api\\Entity';
    }

    /**
     * Generate property comment
     */
    private function generateComment(ColumnMetadata $column, array $config): string
    {
        $comment = $column->getComment();

        if (empty($comment)) {
            $propertyName = $this->generatePropertyName($column->getName());
            $comment = ucfirst(str_replace(['-', '_'], ' ', $propertyName));
        }

        return $comment;
    }

    /**
     * Get orphanRemoval setting from config
     */
    private function getOrphanRemoval(ColumnMetadata $column, array $config): bool
    {
        // First check if there's a fieldConfig key (new format)
        $columnConfig = $config['fieldConfig'] ?? null;

        if ($columnConfig instanceof \Dvsa\Olcs\Cli\Service\EntityGenerator\ValueObjects\FieldConfig) {
            return $columnConfig->orphanRemoval;
        }

        // Fallback to column name lookup (legacy format)
        $columnName = $column->getName();
        $columnConfig = $config[$columnName] ?? null;

        if ($columnConfig instanceof \Dvsa\Olcs\Cli\Service\EntityGenerator\ValueObjects\FieldConfig) {
            return $columnConfig->orphanRemoval;
        }

        if (is_array($columnConfig) && isset($columnConfig['orphanRemoval'])) {
            return (bool) $columnConfig['orphanRemoval'];
        }

        return false;
    }

    /**
     * Get indexBy setting from config
     */
    private function getIndexBy(ColumnMetadata $column, array $config): ?string
    {
        // First check if there's a fieldConfig key (new format)
        $columnConfig = $config['fieldConfig'] ?? null;

        if ($columnConfig instanceof \Dvsa\Olcs\Cli\Service\EntityGenerator\ValueObjects\FieldConfig) {
            return $columnConfig->indexBy;
        }

        // Fallback to column name lookup (legacy format)
        $columnName = $column->getName();
        $columnConfig = $config[$columnName] ?? null;

        if ($columnConfig instanceof \Dvsa\Olcs\Cli\Service\EntityGenerator\ValueObjects\FieldConfig) {
            return $columnConfig->indexBy;
        }

        if (is_array($columnConfig) && isset($columnConfig['indexBy'])) {
            return $columnConfig['indexBy'];
        }

        return null;
    }

    /**
     * Get orderBy setting from config
     */
    private function getOrderBy(ColumnMetadata $column, array $config): array
    {
        // First check if there's a fieldConfig key (new format)
        $columnConfig = $config['fieldConfig'] ?? null;

        if ($columnConfig instanceof \Dvsa\Olcs\Cli\Service\EntityGenerator\ValueObjects\FieldConfig) {
            return $columnConfig->orderBy;
        }

        // Fallback to column name lookup (legacy format)
        $columnName = $column->getName();
        $columnConfig = $config[$columnName] ?? null;

        if ($columnConfig instanceof \Dvsa\Olcs\Cli\Service\EntityGenerator\ValueObjects\FieldConfig) {
            return $columnConfig->orderBy;
        }

        if (is_array($columnConfig) && isset($columnConfig['orderBy'])) {
            return $columnConfig['orderBy'];
        }

        return [];
    }
}
