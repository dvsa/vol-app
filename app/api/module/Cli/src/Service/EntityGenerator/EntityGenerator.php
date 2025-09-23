<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\EntityGenerator;

use Dvsa\Olcs\Cli\Service\EntityGenerator\Exceptions\EntityConfigException;
use Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces\EntityData;
use Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces\EntityGeneratorInterface;
use Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces\GenerationResult;
use Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces\TableMetadata;
use Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces\TypeHandlerRegistryInterface;
use Dvsa\Olcs\Cli\Service\EntityGenerator\ValueObjects\FieldConfig;

/**
 * Enhanced entity generator with EntityConfig support
 */
class EntityGenerator implements EntityGeneratorInterface
{
    public function __construct(
        private readonly TypeHandlerRegistryInterface $typeHandlerRegistry,
        private readonly TemplateRenderer $templateRenderer,
        private readonly EntityConfigService $entityConfigService,
        private readonly InverseRelationshipProcessor $inverseRelationshipProcessor,
        private readonly PropertyNameResolver $propertyNameResolver
    ) {}


    public function generateEntities(array $tables, array $config): GenerationResult
    {
        $startTime = microtime(true);
        $result = new GenerationResult();

        // Filter out tables that should be ignored
        $filteredTables = array_filter($tables, function (TableMetadata $table): bool {
            return !$this->entityConfigService->shouldIgnoreTable($table->getTableName());
        });

        // First pass: Generate base entities
        foreach ($filteredTables as $table) {
            try {
                $entityData = $this->generateEntity($table, $config);
                $result->addEntity($entityData);
            } catch (EntityConfigException $e) {
                $result->addError(
                    sprintf('Config error for table %s: %s', $table->getTableName(), $e->getMessage())
                );
            } catch (\RuntimeException $e) {
                $result->addError(
                    sprintf('Runtime error for table %s: %s', $table->getTableName(), $e->getMessage())
                );
            } catch (\Exception $e) {
                // Only catch truly unexpected exceptions
                $result->addError(
                    sprintf('Unexpected error for table %s: %s', $table->getTableName(), $e->getMessage())
                );
            }
        }

        // Second pass: Add inverse relationships
        $this->addInverseRelationships($result, $filteredTables, $config);

        $result->setDuration(microtime(true) - $startTime);
        return $result;
    }

    public function generateEntity(TableMetadata $table, array $config): EntityData
    {
        $tableName = $table->getTableName();
        $className = $this->generateClassName($tableName, $config);
        $namespace = $this->getNamespace($className, $config);
        
        $relativeNamespace = $this->getRelativeNamespace($className, $config);
        $entityData = new EntityData(
            $tableName,
            $className,
            $namespace,
            $relativeNamespace
        );

        // Process columns and generate fields with EntityConfig support
        $fields = $this->processColumnsWithConfig($table, $config);
        
        // Generate abstract entity
        $abstractContent = $this->generateAbstractEntity($entityData, $table, $fields, $config);
        $entityData->setAbstractContent($abstractContent);

        // Generate concrete entity (only if it doesn't exist)
        $concreteContent = $this->generateConcreteEntity($entityData, $config);
        $entityData->setConcreteContent($concreteContent);

        // Generate test content for entities
        $testContent = $this->generateEntityTest($entityData, $config);
        $entityData->setTestContent($testContent);

        // Store enhanced metadata
        $entityData->setMetadata([
            'fields' => $fields,
            'table' => $table,
            'hasCollections' => $this->hasCollections($fields),
            'hasCreatedOn' => $this->hasField($fields, 'createdOn'),
            'hasModifiedOn' => $this->hasField($fields, 'lastModifiedOn'),
            'softDeletable' => $this->hasSoftDeletable($table, $config),
            'entityConfig' => $this->entityConfigService->getTableConfig($tableName),
            'collections' => $this->getCollections($fields),
        ]);

        return $entityData;
    }

    /**
     * Process all columns in the table with EntityConfig support
     */
    private function processColumnsWithConfig(TableMetadata $table, array $config): array
    {
        $fields = [];
        $tableName = $table->getTableName();
        $tableConfig = $this->entityConfigService->getTableConfig($tableName);

        foreach ($table->getColumns() as $column) {
            $columnName = $column->getName();
            $fieldConfig = $tableConfig[$columnName] ?? null;
            
            // Set table metadata on RelationshipTypeHandler BEFORE getting handler
            $this->setTableOnRelationshipHandlers($table);
            
            // Get the appropriate type handler with EntityConfig support
            $handler = $this->typeHandlerRegistry->getHandler($column, $fieldConfig ? ['fieldConfig' => $fieldConfig] : []);
            
            if ($handler === null) {
                throw new \RuntimeException(
                    sprintf('No type handler found for column %s.%s', $tableName, $columnName)
                );
            }

            // Generate field data with EntityConfig integration
            $fieldData = [
                'column' => $column,
                'handler' => $handler,
                'fieldConfig' => $fieldConfig,
                'annotation' => $this->generateFieldAnnotation($column, $handler, $fieldConfig, $config),
                'property' => $this->generateFieldProperty($column, $handler, $fieldConfig, $tableName, $config),
                'isRelationship' => $this->isRelationshipField($column, $table),
                'isCollection' => false, // Will be set to true for inverse relationships
            ];

            $fields[] = $fieldData;
        }

        // Check if ManyToMany relationships should be skipped for this entity
        $className = $this->generateClassName($tableName, $config);
        $namespace = $this->getNamespace($className, $config);
        $entityClass = $namespace . '\\' . $className;
        
        if ($this->entityConfigService->shouldSkipManyToMany($entityClass)) {
            // Skip adding ManyToMany relationships for this entity
            return $fields;
        }

        // Add ManyToMany relationships if present
        if (isset($config['relationships'][$tableName])) {
            foreach ($config['relationships'][$tableName] as $relationship) {
                if ($relationship['type'] === 'many_to_many') {
                    $field = $this->createManyToManyField($relationship, $tableName, $config);
                    if ($field !== null) {
                        $fields[] = $field;
                    }
                }
            }
        }

        return $fields;
    }

    /**
     * Generate field annotation with EntityConfig support
     */
    private function generateFieldAnnotation($column, $handler, $fieldConfig, array $config = []): string
    {
        // Use enhanced annotation method if available
        if (method_exists($handler, 'generateAnnotationWithConfig')) {
            return $handler->generateAnnotationWithConfig($column, $fieldConfig);
        }

        // Fall back to legacy method
        $handlerConfig = ['fieldConfig' => $fieldConfig];
        if (!empty($config)) {
            $handlerConfig = array_merge($handlerConfig, $config);
        }
        return $handler->generateAnnotation($column, $handlerConfig);
    }

    /**
     * Generate field property with EntityConfig support
     */
    private function generateFieldProperty($column, $handler, $fieldConfig, $tableName, array $config = []): array
    {
        $handlerConfig = ['fieldConfig' => $fieldConfig];
        if (!empty($config)) {
            $handlerConfig = array_merge($handlerConfig, $config);
        }
        $property = $handler->generateProperty($column, $handlerConfig);
        
        // Check for property name override in field config first (e.g., address table)
        if ($fieldConfig && $fieldConfig->property !== null) {
            $property['name'] = $fieldConfig->property;
        } else {
            // Otherwise check fieldNameForColumn mapping
            $customPropertyName = $this->entityConfigService->getFieldNameForColumn($tableName, $column->getName());
            if ($customPropertyName !== null) {
                $property['name'] = $customPropertyName;
            }
        }

        return $property;
    }

    /**
     * Check if this is a relationship field
     */
    private function isRelationshipField($column, TableMetadata $table): bool
    {
        // Check if this column is a foreign key
        foreach ($table->getForeignKeys() as $fk) {
            // Handle both array and object structures for foreign keys
            $localColumns = is_array($fk) ? ($fk['localColumns'] ?? []) : $fk->getLocalColumns();
            if (in_array($column->getName(), $localColumns)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add inverse relationships to generated entities
     */
    private function addInverseRelationships(GenerationResult $result, array $tables, array $config): void
    {
        // Get list of join table names from relationships config
        $joinTableNames = [];
        if (isset($config['relationships'])) {
            foreach ($config['relationships'] as $tableName => $tableRelationships) {
                foreach ($tableRelationships as $relationship) {
                    if ($relationship['type'] === 'many_to_many' && isset($relationship['join_table'])) {
                        $joinTableNames[] = $relationship['join_table'];
                    }
                }
            }
        }
        $joinTableNames = array_unique($joinTableNames);
        
        $inverseRelationships = $this->inverseRelationshipProcessor->processInverseRelationships($tables, $joinTableNames);

        foreach ($result->getEntities() as $entityData) {
            $className = $entityData->getClassName();
            
            if (isset($inverseRelationships[$className])) {
                $this->addInverseRelationshipsToEntity($entityData, $inverseRelationships[$className]);
            }
        }
    }

    /**
     * Add inverse relationships to a specific entity
     */
    private function addInverseRelationshipsToEntity(EntityData $entityData, array $relationships): void
    {
        $metadata = $entityData->getMetadata();
        $fields = $metadata['fields'] ?? [];
        $collections = $metadata['collections'] ?? [];

        foreach ($relationships as $relationship) {
            // Create inverse relationship field
            // Determine the proper type for the relationship
            $propertyType = $relationship['relationshipType']->isCollection() 
                ? '\\Doctrine\\Common\\Collections\\ArrayCollection'
                : '\\Dvsa\\Olcs\\Api\\Entity\\' . $relationship['sourceEntity'];
            
            // Generate annotations
            $annotations = [$this->inverseRelationshipProcessor->generateInverseAnnotation($relationship)];
            
            // Add OrderBy annotation if specified
            if (!empty($relationship['orderBy'])) {
                $orderByAnnotation = $this->inverseRelationshipProcessor->generateOrderByAnnotation($relationship['orderBy']);
                if ($orderByAnnotation) {
                    $annotations[] = $orderByAnnotation;
                }
            }
            
            $inverseField = [
                'column' => null, // No direct column for inverse relationships
                'handler' => null,
                'fieldConfig' => null,
                'annotation' => implode("\n     * ", $annotations),
                'property' => [
                    'name' => $relationship['property'],
                    'type' => $propertyType,
                    'docBlock' => ucfirst(str_replace('_', ' ', $relationship['property'])),
                    'nullable' => false,
                    'isRelationship' => true,
                    'defaultValue' => 'null',
                ],
                'isRelationship' => true,
                'isCollection' => $relationship['relationshipType']->isCollection(),
                'isInverse' => true,
                'type' => $relationship['relationshipType']->value, // Add relationship type for template
                'relationship' => $relationship['relationshipType']->value, // Alternative key
            ];

            $fields[] = $inverseField;

            // If it's a collection relationship, add to collections array
            if ($relationship['relationshipType']->isCollection()) {
                $collections[] = [
                    'property' => $relationship['property'],
                    'annotation' => $inverseField['annotation'],
                ];
            }
        }

        // Update metadata
        $metadata['fields'] = $fields;
        $metadata['collections'] = $collections;
        $metadata['hasCollections'] = !empty($collections);
        $entityData->setMetadata($metadata);

        // Regenerate abstract entity content with inverse relationships
        $abstractContent = $this->generateAbstractEntity($entityData, $metadata['table'], $fields, []);
        $entityData->setAbstractContent($abstractContent);
    }

    /**
     * Get collections from fields
     */
    private function getCollections(array $fields): array
    {
        $collections = [];
        
        foreach ($fields as $field) {
            if ($field['isCollection'] ?? false) {
                $collections[] = [
                    'property' => $field['property']['name'],
                    'annotation' => $field['annotation'],
                ];
            }
        }

        return $collections;
    }

    /**
     * Generate abstract entity content
     */
    private function generateAbstractEntity(EntityData $entityData, TableMetadata $table, array $fields, array $config): string
    {
        $templateData = [
            'namespace' => $entityData->getNamespace(),
            'className' => $entityData->getAbstractClassName(),
            'tableName' => $table->getTableName(),
            'fields' => $fields,
            'indexes' => $table->getIndexes(),
            'uniqueConstraints' => $table->getUniqueConstraints(),
            'hasCollections' => $this->hasCollections($fields),
            'hasCreatedOn' => $this->hasField($fields, 'createdOn'),
            'hasModifiedOn' => $this->hasField($fields, 'lastModifiedOn'),
            'softDeletable' => $this->hasSoftDeletable($table, $config),
            'imports' => $this->gatherImports($fields),
            'repositoryClass' => $this->getRepositoryClass($table),
        ];

        return $this->templateRenderer->render('abstract-entity', $templateData);
    }

    /**
     * Generate concrete entity content
     */
    private function generateConcreteEntity(EntityData $entityData, array $config): string
    {
        $metadata = $entityData->getMetadata();
        $table = $metadata['table'] ?? null;
        
        $templateData = [
            'namespace' => $entityData->getNamespace(),
            'className' => $entityData->getClassName(),
            'abstractClassName' => $entityData->getAbstractClassName(),
            'tableName' => $entityData->getTableName(),
            'repositoryClass' => $table ? $this->getRepositoryClass($table) : null,
        ];

        return $this->templateRenderer->render('concrete-entity', $templateData);
    }

    /**
     * Generate entity test content
     */
    private function generateEntityTest(EntityData $entityData, array $config): string
    {
        // Convert entity namespace to test namespace
        // From: Dvsa\Olcs\Api\Entity\System
        // To:   Dvsa\OlcsTest\Api\Entity\System
        $testNamespace = str_replace('Dvsa\\Olcs\\Api\\Entity', 'Dvsa\\OlcsTest\\Api\\Entity', $entityData->getNamespace());
        
        $templateData = [
            'namespace' => $testNamespace,
            'className' => $entityData->getClassName(),
            'entityNamespace' => $entityData->getNamespace(),
            'entityClass' => $entityData->getClassName(),
        ];

        return $this->templateRenderer->render('entity-test', $templateData);
    }

    /**
     * Generate class name from table name
     */
    private function generateClassName(string $tableName, array $config): string
    {
        // Check for custom class name mapping
        $classMapping = $config['mappingConfig']['classNameForTable'] ?? [];
        if (isset($classMapping[$tableName])) {
            return $classMapping[$tableName];
        }

        // Convert snake_case to PascalCase
        return str_replace('_', '', ucwords($tableName, '_'));
    }

    /**
     * Get namespace for the entity
     */
    private function getNamespace(string $className, array $config): string
    {
        $namespaces = $config['namespaces'] ?? [];
        
        // Check if the className is directly mapped to a namespace
        if (isset($namespaces[$className])) {
            return 'Dvsa\\Olcs\\Api\\Entity\\' . $namespaces[$className];
        }

        // Default to root Entity namespace (no sub-namespace)
        return 'Dvsa\\Olcs\\Api\\Entity';
    }

    /**
     * Get relative namespace folder for the entity
     */
    private function getRelativeNamespace(string $className, array $config): string
    {
        $namespaces = $config['namespaces'] ?? [];
        
        // Check if the className is directly mapped to a namespace
        if (isset($namespaces[$className])) {
            return $namespaces[$className];
        }

        // Default to empty string (root Entity folder)
        return '';
    }

    /**
     * Create a ManyToMany field from relationship data
     */
    private function createManyToManyField(array $relationship, string $tableName, array $config): ?array
    {
        // Get the foreign table name and convert to entity name
        $foreignTable = $relationship['foreign_table'];
        $entityName = $this->generateClassName($foreignTable, []);
        
        // Get namespace for the target entity from entity config
        $namespace = $this->entityConfigService->getEntityNamespace($entityName) ?? 'Generic';
        $targetEntityClass = 'Dvsa\\Olcs\\Api\\Entity\\' . $namespace . '\\' . $entityName;
        
        // Check if ManyToMany relationships should be skipped for the target entity
        if ($this->entityConfigService->shouldSkipManyToMany($targetEntityClass)) {
            // Skip creating ManyToMany relationship to this target entity
            return null;
        }
        
        // Check if there's an inversedBy configuration for this join table
        // This happens when a join table also has an entity configuration
        $joinTableConfig = $this->entityConfigService->getTableConfig($relationship['join_table']);
        $propertyNameFromConfig = null;
        
        // Look for the foreign key that points back to our table
        foreach ($joinTableConfig as $columnName => $fieldConfig) {
            if ($fieldConfig instanceof FieldConfig && 
                $fieldConfig->inversedBy !== null && 
                $fieldConfig->inversedBy->entity === $this->generateClassName($tableName, [])) {
                // Use the property name from the inversedBy configuration
                $propertyNameFromConfig = $fieldConfig->inversedBy->property;
                break;
            }
        }
        
        if ($propertyNameFromConfig !== null) {
            // Check if target entity is NOT RefData
            if ($entityName !== 'RefData') {
                // For non-RefData entities, ignore EntityConfig and use target entity name
                // This fixes incorrect mappings like licenceStatusDecision -> decisions
                $basePropertyName = lcfirst($entityName);
                $propertyName = $this->propertyNameResolver->resolvePropertyName($basePropertyName, true);
            } else {
                // For RefData, pluralize the EntityConfig property name since it's a collection
                // e.g., ground -> grounds, actionType -> actionTypes
                $propertyName = $this->propertyNameResolver->resolvePropertyName($propertyNameFromConfig, true);
            }
        } else {
            // No EntityConfig - derive property name appropriately
            if ($entityName === 'RefData') {
                // For RefData without config, derive from join column name for descriptive naming
                $inverseJoinColumn = $relationship['inverse_join_columns'][0] ?? null;
                if ($inverseJoinColumn) {
                    // Remove _id suffix if present, otherwise use whole column name
                    $basePropertyName = preg_replace('/_id$/', '', $inverseJoinColumn);
                    // Convert snake_case to camelCase (e.g., action_type -> actionType)
                    $basePropertyName = lcfirst(str_replace('_', '', ucwords($basePropertyName, '_')));
                    $propertyName = $this->propertyNameResolver->resolvePropertyName($basePropertyName, true);
                } else {
                    // Fallback to entity name
                    $basePropertyName = lcfirst($entityName);
                    $propertyName = $this->propertyNameResolver->resolvePropertyName($basePropertyName, true);
                }
            } else {
                // For non-RefData, use target entity name
                $basePropertyName = lcfirst($entityName);
                $propertyName = $this->propertyNameResolver->resolvePropertyName($basePropertyName, true);
            }
        }
        
        // Get namespace for the target entity from entity config
        $namespace = $this->entityConfigService->getEntityNamespace($entityName) ?? 'Generic';
        $targetEntity = 'Dvsa\\Olcs\\Api\\Entity\\' . $namespace . '\\' . $entityName;
        
        // Use the is_owning flag if it exists, otherwise determine from join columns
        if (isset($relationship['is_owning'])) {
            $isOwning = $relationship['is_owning'];
        } else {
            // Fallback to original logic for backwards compatibility
            $firstJoinColumn = $relationship['join_columns'][0] ?? null;
            if ($firstJoinColumn && preg_match('/^(.+?)_id$/', $firstJoinColumn, $matches)) {
                // Check if the first column's table name matches our current table
                $firstColumnTable = $matches[1];
                $isOwning = ($firstColumnTable === $tableName);
            } else {
                // Fallback to alphabetical ordering if we can't determine from columns
                $isOwning = strcmp($tableName, $foreignTable) < 0;
            }
        }
        
        // Generate the inverse property name
        if (!$isOwning) {
            // For inverse side, mappedBy should reference the property name on the owning side
            // The owning side generates its property name from the inverse entity (this entity)
            // We need to use the same logic the owning side would use
            $entityNameForProperty = $this->generateClassName($tableName, $config);
            $basePropertyName = lcfirst($entityNameForProperty);
            $inversePropertyName = $this->propertyNameResolver->resolvePropertyName($basePropertyName, true);
        } else {
            // For owning side, generate the inverse property name for inversedBy  
            $inversePropertyName = $this->generateInversePropertyName($tableName);
        }
        
        // Build annotation based on ownership
        $annotation = $isOwning 
            ? $this->buildOwningManyToManyAnnotation($targetEntity, $inversePropertyName, $relationship)
            : $this->buildInverseManyToManyAnnotation($targetEntity, $inversePropertyName);
        
        return $this->buildManyToManyFieldArray($propertyName, $annotation);
    }

    /**
     * Check if foreign table has inversedBy configuration pointing to this table
     */
    private function hasInversedByConfiguration(string $foreignTable, string $tableName): bool
    {
        $tableConfig = $this->entityConfigService->getTableConfig($foreignTable);
        $targetEntityName = $this->generateClassName($tableName, []);
        
        foreach ($tableConfig as $fieldName => $fieldConfig) {
            if ($fieldConfig instanceof FieldConfig && 
                $fieldConfig->inversedBy !== null && 
                $fieldConfig->inversedBy->entity === $targetEntityName) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Generate inverse property name for ManyToMany relationship
     */
    private function generateInversePropertyName(string $tableName): string
    {
        $inverseBasePropertyName = lcfirst($this->generateClassName($tableName, []));
        return $this->propertyNameResolver->resolvePropertyName($inverseBasePropertyName, true);
    }

    /**
     * Build owning side ManyToMany annotation
     */
    private function buildOwningManyToManyAnnotation(string $targetEntity, string $inversePropertyName, array $relationship): string
    {
        return sprintf(
            '@ORM\ManyToMany(targetEntity="%s", inversedBy="%s", fetch="LAZY")' . "\n" .
            '     * @ORM\JoinTable(name="%s",' . "\n" .
            '     *     joinColumns={' . "\n" .
            '     *         @ORM\JoinColumn(name="%s", referencedColumnName="%s")' . "\n" .
            '     *     },' . "\n" .
            '     *     inverseJoinColumns={' . "\n" .
            '     *         @ORM\JoinColumn(name="%s", referencedColumnName="%s")' . "\n" .
            '     *     }' . "\n" .
            '     * )',
            $targetEntity,
            $inversePropertyName,
            $relationship['join_table'],
            $relationship['join_columns'][0],
            $relationship['local_columns'][0],
            $relationship['inverse_join_columns'][0],
            $relationship['foreign_columns'][0]
        );
    }

    /**
     * Build inverse side ManyToMany annotation
     */
    private function buildInverseManyToManyAnnotation(string $targetEntity, string $propertyName): string
    {
        return sprintf(
            '@ORM\ManyToMany(targetEntity="%s", mappedBy="%s", fetch="LAZY")',
            $targetEntity,
            $propertyName
        );
    }

    /**
     * Build ManyToMany field array structure
     */
    private function buildManyToManyFieldArray(string $propertyName, string $annotation): array
    {
        return [
            'column' => null,
            'handler' => null,
            'fieldConfig' => null,
            'annotation' => $annotation,
            'property' => [
                'name' => $propertyName,
                'type' => '\\Doctrine\\Common\\Collections\\ArrayCollection',
                'docBlock' => ucfirst(str_replace('_', ' ', $propertyName)),
                'defaultValue' => 'null',
                'nullable' => false,
                'isRelationship' => true,
            ],
            'isRelationship' => true,
            'isCollection' => true,
            'type' => 'manyToMany',
            'relationship' => 'manyToMany',
        ];
    }

    /**
     * Check if any fields represent collections (relationships)
     */
    private function hasCollections(array $fields): bool
    {
        foreach ($fields as $field) {
            if ($field['property']['isRelationship'] ?? false) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if a specific field exists
     */
    private function hasField(array $fields, string $fieldName): bool
    {
        foreach ($fields as $field) {
            if ($field['property']['name'] === $fieldName) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check if soft deletable based on table or config
     */
    private function hasSoftDeletable(TableMetadata $table, array $config): bool
    {
        // Check if table has deleted_date column
        return $table->getColumn('deleted_date') !== null;
    }

    /**
     * Gather all required imports from handlers
     */
    private function gatherImports(array $fields): array
    {
        $imports = [];
        $hasTranslatable = false;
        
        foreach ($fields as $field) {
            // Only gather imports from fields that have handlers
            if ($field['handler'] !== null) {
                $handlerImports = $field['handler']->getRequiredImports();
                $imports = array_merge($imports, $handlerImports);
            }
            
            // Check if field is translatable
            if ($field['fieldConfig'] && $field['fieldConfig']->translatable) {
                $hasTranslatable = true;
            }
        }

        // Add Gedmo import if we have translatable fields
        if ($hasTranslatable) {
            $imports[] = 'Gedmo\Mapping\Annotation as Gedmo';
        }

        return array_unique($imports);
    }

    /**
     * Get repository class from table comment
     */
    private function getRepositoryClass(TableMetadata $table): ?string
    {
        $comment = $table->getComment();
        if (empty($comment)) {
            return null;
        }

        // Look for @settings['repository'] in the comment
        if (preg_match('/@settings\s*\[\s*[\'"]repository[\'"]\s*\]\s*=\s*[\'"]([^\'"\s]+)[\'"]/', $comment, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Check if table should be ignored based on comment
     */
    private function shouldIgnoreTableByComment(TableMetadata $table): bool
    {
        $comment = $table->getComment();
        if (empty($comment)) {
            return false;
        }

        // Look for @settings['ignore'] in the comment
        return preg_match('/@settings\s*\[\s*[\'"]ignore[\'"]\s*\]/', $comment) === 1;
    }

    /**
     * Set table metadata on all RelationshipTypeHandlers in the registry
     */
    private function setTableOnRelationshipHandlers(TableMetadata $table): void
    {
        foreach ($this->typeHandlerRegistry->getHandlers() as $handler) {
            if ($handler instanceof \Dvsa\Olcs\Cli\Service\EntityGenerator\TypeHandlers\RelationshipTypeHandler) {
                $handler->setCurrentTable($table);
            }
        }
    }
}