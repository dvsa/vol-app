<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\EntityGenerator\TypeHandlers;

use Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces\ColumnMetadata;

/**
 * Type handler for relationship columns (foreign keys)
 */
class RelationshipTypeHandler extends AbstractTypeHandler
{
    public function supports(ColumnMetadata $column, array $config = []): bool
    {
        // Check if this column is a foreign key
        return str_ends_with($column->getName(), '_id') && 
               $column->getName() !== 'id' && 
               !str_starts_with($column->getName(), 'ol_');
    }

    public function generateAnnotation(ColumnMetadata $column, array $config = []): string
    {
        $targetEntity = $this->getTargetEntity($column, $config);
        $referencedColumn = $this->getReferencedColumn($column, $config);
        
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

        return sprintf(
            "@ORM\ManyToOne(%s)\n     * @ORM\JoinColumn(%s)",
            implode(', ', $options),
            implode(', ', $joinOptions)
        );
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

        // Try to derive from column name
        if (preg_match('/^(.+)_id$/', $columnName, $matches)) {
            $entityName = $this->columnNameToEntityName($matches[1]);
            
            // Check if there's a namespace mapping
            $namespace = $this->getEntityNamespace($entityName, $config);
            
            return $namespace . '\\' . $entityName;
        }

        // Fallback to RefData for unrecognized patterns
        return 'Dvsa\\Olcs\\Api\\Entity\\System\\RefData';
    }

    /**
     * Get referenced column name
     */
    private function getReferencedColumn(ColumnMetadata $column, array $config): string
    {
        // Check config for custom referenced column
        if (isset($config['referencedColumn'][$column->getName()])) {
            return $config['referencedColumn'][$column->getName()];
        }

        // Default to 'id'
        return 'id';
    }

    /**
     * Get cascade options from config
     */
    private function getCascadeOptions(ColumnMetadata $column, array $config): array
    {
        return $config['cascade'][$column->getName()] ?? [];
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
     * Get entity namespace based on entity name
     */
    private function getEntityNamespace(string $entityName, array $config): string
    {
        $namespaces = $config['namespaces'] ?? [];
        
        foreach ($namespaces as $namespace => $entities) {
            if (in_array($entityName, $entities)) {
                return $namespace;
            }
        }

        // Default namespace
        return 'Dvsa\\Olcs\\Api\\Entity\\System';
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
}