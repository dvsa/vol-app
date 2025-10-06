<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\EntityGenerator;

use Dvsa\Olcs\Cli\Service\EntityGenerator\Enums\RelationshipType;
use Dvsa\Olcs\Cli\Service\EntityGenerator\ValueObjects\FieldConfig;
use Dvsa\Olcs\Cli\Service\EntityGenerator\ValueObjects\InversedByConfig;
use Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces\TableMetadata;

/**
 * Processes EntityConfig inversedBy configurations to generate OneToMany relationships
 */
readonly class InverseRelationshipProcessor
{
    public function __construct(
        private EntityConfigService $configService,
        private PropertyNameResolver $propertyNameResolver
    ) {}

    /**
     * Process all inverse relationships and return them grouped by target entity
     */
    public function processInverseRelationships(array $tables, array $joinTableNames = []): array
    {
        $inverseRelationships = [];

        foreach ($tables as $table) {
            $tableName = $table->getTableName();
            
            // Skip processing inverse relationships for join tables
            // Join tables should only create ManyToMany relationships, not OneToMany
            if (in_array($tableName, $joinTableNames)) {
                continue;
            }
            
            $tableConfig = $this->configService->getTableConfig($tableName);

            foreach ($table->getColumns() as $column) {
                $columnName = $column->getName();
                $fieldConfig = $tableConfig[$columnName] ?? null;

                if ($fieldConfig?->inversedBy !== null) {
                    $this->processInverseRelationship(
                        $inverseRelationships,
                        $tableName,
                        $columnName,
                        $fieldConfig,
                        $table
                    );
                }
            }
        }

        // ManyToMany relationships are now detected automatically from join tables
        // by the Doctrine3SchemaIntrospector, so we don't need to hardcode them here

        return $inverseRelationships;
    }


    /**
     * Process a single inverse relationship configuration
     */
    private function processInverseRelationship(
        array &$inverseRelationships,
        string $sourceTable,
        string $sourceColumn,
        FieldConfig $fieldConfig,
        TableMetadata $table
    ): void {
        $inversedByConfig = $fieldConfig->inversedBy;
        $targetEntity = $inversedByConfig->entity;

        // Determine the relationship type
        $relationshipType = $this->determineInverseRelationshipType($sourceTable, $sourceColumn, $table);

        // Get the source entity name
        $sourceEntityName = $this->getEntityNameFromTable($sourceTable);

        // Resolve the property name with proper pluralization
        $isCollection = $relationshipType->isCollection();
        $resolvedPropertyName = $this->propertyNameResolver->resolvePropertyName(
            $inversedByConfig->property,
            $isCollection,
            $inversedByConfig->property
        );

        $inverseRelationships[$targetEntity] ??= [];
        $inverseRelationships[$targetEntity][] = [
            'property' => $resolvedPropertyName,
            'sourceEntity' => $sourceEntityName,
            'sourceTable' => $sourceTable,
            'sourceColumn' => $sourceColumn,
            'mappedBy' => $this->generatePropertyName($sourceColumn),
            'relationshipType' => $relationshipType,
            'cascade' => $inversedByConfig->cascade,
            'fetch' => $inversedByConfig->fetch,
            'indexBy' => $inversedByConfig->indexBy,
            'orphanRemoval' => $inversedByConfig->orphanRemoval,
            'orderBy' => $inversedByConfig->orderBy
        ];
    }

    /**
     * Determine the inverse relationship type based on the source relationship
     */
    private function determineInverseRelationshipType(
        string $sourceTable,
        string $sourceColumn,
        TableMetadata $table
    ): RelationshipType {
        // Check if this is a foreign key column
        $foreignKeys = $table->getForeignKeys();
        
        foreach ($foreignKeys as $fk) {
            // Handle both array and object structures for foreign keys
            $localColumns = is_array($fk) ? ($fk['localColumns'] ?? $fk['local_columns'] ?? []) : $fk->getLocalColumns();
            
            if (in_array($sourceColumn, $localColumns)) {
                // Check if this FK column is part of a unique constraint
                $uniqueConstraints = $table->getUniqueConstraints();
                
                foreach ($uniqueConstraints as $uc) {
                    $columns = is_array($uc) ? ($uc['columns'] ?? []) : $uc->getColumns();
                    
                    // Check if the foreign key column is in any unique constraint
                    if (in_array($sourceColumn, $columns)) {
                        // If it's a single-column unique constraint, it's definitely OneToOne
                        if (count($columns) === 1) {
                            return RelationshipType::ONE_TO_ONE;
                        }
                        // Multi-column unique constraints might also indicate OneToOne in some cases
                        // but we'll be conservative and only treat single-column unique as OneToOne
                    }
                }
                
                // Regular FK without unique constraint = ManyToOne (so inverse is OneToMany)
                return RelationshipType::ONE_TO_MANY;
            }
        }

        // Default to OneToMany
        return RelationshipType::ONE_TO_MANY;
    }

    /**
     * Generate entity name from table name
     */
    private function getEntityNameFromTable(string $tableName): string
    {
        // Check for custom class name mapping
        $customName = $this->configService->getClassNameForTable($tableName);
        if ($customName !== null) {
            return $customName;
        }

        // Convert snake_case to PascalCase
        return str_replace('_', '', ucwords($tableName, '_'));
    }

    /**
     * Generate property name from column name
     */
    private function generatePropertyName(string $columnName): string
    {
        // Remove common suffixes
        $columnName = preg_replace('/_id$/', '', $columnName);
        
        // Convert to camelCase
        return lcfirst(str_replace('_', '', ucwords($columnName, '_')));
    }

    /**
     * Generate annotation for inverse relationship
     */
    public function generateInverseAnnotation(array $relationshipData): string
    {
        $relationshipType = $relationshipData['relationshipType'];
        $sourceEntity = $relationshipData['sourceEntity'];
        $mappedBy = $relationshipData['mappedBy'];

        $options = [];
        $options[] = sprintf('targetEntity="Dvsa\\Olcs\\Api\\Entity\\%s\\%s"', 
            $this->getEntityNamespace($sourceEntity), 
            $sourceEntity
        );
        $options[] = sprintf('mappedBy="%s"', $mappedBy);

        // Add cascade if specified
        if (!empty($relationshipData['cascade'])) {
            $cascadeOptions = array_map(fn($c) => '"' . $c . '"', $relationshipData['cascade']);
            $options[] = 'cascade={' . implode(', ', $cascadeOptions) . '}';
        }

        // Add fetch strategy if specified
        if ($relationshipData['fetch'] !== null) {
            $options[] = sprintf('fetch="%s"', strtoupper($relationshipData['fetch']));
        }

        // Add indexBy if specified
        if ($relationshipData['indexBy'] !== null) {
            $options[] = sprintf('indexBy="%s"', $relationshipData['indexBy']);
        }

        // Add orphanRemoval if specified
        if ($relationshipData['orphanRemoval']) {
            $options[] = 'orphanRemoval=true';
        }

        $optionsStr = implode(', ', $options);

        return sprintf('@ORM\\%s(%s)', 
            ucfirst($relationshipType->value), 
            $optionsStr
        );
    }

    /**
     * Generate order by annotation if specified
     */
    public function generateOrderByAnnotation(array $orderBy): string|null
    {
        if (empty($orderBy)) {
            return null;
        }

        $orderPairs = [];
        foreach ($orderBy as $field => $direction) {
            $orderPairs[] = sprintf('"%s" = "%s"', $field, strtoupper($direction));
        }

        return sprintf('@ORM\\OrderBy({%s})', implode(', ', $orderPairs));
    }

    /**
     * Get entity namespace from entity name
     */
    private function getEntityNamespace(string $entityName): string
    {
        $namespace = $this->configService->getEntityNamespace($entityName);
        return $namespace ?? 'Generic';
    }
}