<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\EntityGenerator\TypeHandlers;

use Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces\ColumnMetadata;

/**
 * Type handler for Doctrine version fields
 */
class VersionTypeHandler extends AbstractTypeHandler
{
    public function supports(ColumnMetadata $column, array $config = []): bool
    {
        return $column->getName() === 'version';
    }

    public function generateAnnotation(ColumnMetadata $column, array $config = []): string
    {
        $annotations = [];
        
        // Add column definition
        $type = $column->getType() === 'smallint' ? 'smallint' : 'integer';
        $options = [];
        
        if ($column->getDefault() !== null) {
            $options[] = '"default": ' . $column->getDefault();
        }
        
        $columnDef = sprintf(
            '@ORM\Column(type="%s", name="%s", nullable=%s',
            $type,
            $column->getName(),
            $column->isNullable() ? 'true' : 'false'
        );
        
        if (!empty($options)) {
            $columnDef .= ', options={' . implode(', ', $options) . '}';
        }
        
        $columnDef .= ')';
        
        $annotations[] = $columnDef;
        
        // Add version annotation
        $annotations[] = '@ORM\Version';
        
        return implode("\n     * ", $annotations);
    }

    public function generateProperty(ColumnMetadata $column, array $config = []): array
    {
        return [
            'name' => 'version',
            'type' => 'int',
            'docBlock' => 'Version',
            'defaultValue' => $column->getDefault() !== null ? (string)$column->getDefault() : '1',
            'nullable' => $column->isNullable(),
            'isRelationship' => false,
        ];
    }

    public function getPriority(): int
    {
        return 90; // High priority
    }

    public function getRequiredImports(): array
    {
        return ['Doctrine\ORM\Mapping as ORM'];
    }
}