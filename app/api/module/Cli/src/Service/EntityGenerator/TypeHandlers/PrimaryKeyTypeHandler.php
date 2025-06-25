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
        // Handle 'id' column specifically
        return $column->getName() === 'id' && $column->isAutoIncrement();
    }

    public function generateAnnotation(ColumnMetadata $column, array $config = []): string
    {
        $annotations = [];
        
        // Add @ORM\Id
        $annotations[] = '@ORM\Id';
        
        // Add column definition
        $type = 'integer';
        $annotations[] = sprintf(
            '@ORM\Column(type="%s", name="%s")',
            $type,
            $column->getName()
        );
        
        // Add generation strategy
        $annotations[] = '@ORM\GeneratedValue(strategy="IDENTITY")';
        
        return implode("\n     * ", $annotations);
    }

    public function generateProperty(ColumnMetadata $column, array $config = []): array
    {
        return [
            'name' => 'id',
            'type' => 'int',
            'docBlock' => 'Identifier - Id',
            'defaultValue' => 'null',
            'nullable' => false,
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