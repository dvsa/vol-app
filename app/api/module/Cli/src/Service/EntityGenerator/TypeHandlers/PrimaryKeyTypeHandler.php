<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\EntityGenerator\TypeHandlers;

use Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces\ColumnMetadata;

/**
 * Type handler for primary key fields
 */
class PrimaryKeyTypeHandler extends AbstractTypeHandler
{
    public function supports(ColumnMetadata $column, array $config = []): bool
    {
        // Handle any primary key column
        return $column->isPrimary();
    }

    public function generateAnnotation(ColumnMetadata $column, array $config = []): string
    {
        $annotations = [];
        
        // Add @ORM\Id
        $annotations[] = '@ORM\Id';
        
        // Add column definition
        $columnDef = sprintf(
            '@ORM\Column(type="%s", name="%s"',
            $column->getType(),
            $column->getName()
        );
        
        // Add length for string types
        if ($column->getLength() && in_array($column->getType(), ['string', 'char'])) {
            $columnDef .= sprintf(', length=%d', $column->getLength());
        }
        
        // Add nullable if needed
        if ($column->isNullable()) {
            $columnDef .= ', nullable=true';
        } else {
            $columnDef .= ', nullable=false';
        }
        
        $columnDef .= ')';
        $annotations[] = $columnDef;
        
        // Add generation strategy only for auto-increment columns
        if ($column->isAutoIncrement()) {
            $annotations[] = '@ORM\GeneratedValue(strategy="IDENTITY")';
        }
        
        return implode("\n     * ", $annotations);
    }

    public function generateProperty(ColumnMetadata $column, array $config = []): array
    {
        // Map database types to PHP types
        $phpType = match($column->getType()) {
            'integer', 'bigint', 'smallint' => 'int',
            'string', 'char', 'varchar' => 'string',
            'boolean' => 'bool',
            'float', 'double', 'decimal' => 'float',
            default => 'mixed'
        };
        
        // Generate appropriate default value
        $defaultValue = 'null';
        if (!$column->isNullable() && !$column->isAutoIncrement()) {
            $defaultValue = match($phpType) {
                'int' => '0',
                'string' => "''",
                'bool' => 'false',
                'float' => '0.0',
                default => 'null'
            };
        }
        
        // Generate descriptive docBlock
        $docBlock = sprintf(
            'Primary key%s',
            $column->isAutoIncrement() ? '.  Auto incremented if numeric.' : ''
        );
        
        return [
            'name' => $column->getName(),
            'type' => $phpType,
            'docBlock' => $docBlock,
            'defaultValue' => $defaultValue,
            'nullable' => $column->isNullable(),
            'isRelationship' => false,
        ];
    }

    public function getPriority(): int
    {
        return 100; // High priority to handle before other handlers
    }

    public function getRequiredImports(): array
    {
        return ['Doctrine\ORM\Mapping as ORM'];
    }
}