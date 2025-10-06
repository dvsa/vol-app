<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\EntityGenerator\TypeHandlers;

use Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces\ColumnMetadata;

/**
 * Type handler for Gedmo Blameable fields (created_by, last_modified_by)
 */
class BlameableTypeHandler extends AbstractTypeHandler
{
    public function supports(ColumnMetadata $column, array $config = []): bool
    {
        return in_array($column->getName(), ['created_by', 'last_modified_by']);
    }

    public function generateAnnotation(ColumnMetadata $column, array $config = []): string
    {
        $annotations = [];
        
        // Add ManyToOne relationship
        $annotations[] = '@ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User", fetch="LAZY")';
        
        // Add JoinColumn
        $annotations[] = sprintf(
            '@ORM\JoinColumn(name="%s", referencedColumnName="id", nullable=true)',
            $column->getName()
        );
        
        // Add Blameable annotation
        if ($column->getName() === 'created_by') {
            $annotations[] = '@Gedmo\Blameable(on="create")';
        } elseif ($column->getName() === 'last_modified_by') {
            $annotations[] = '@Gedmo\Blameable(on="update")';
        }
        
        return implode("\n     * ", $annotations);
    }

    public function generateProperty(ColumnMetadata $column, array $config = []): array
    {
        $propertyName = $this->generatePropertyName($column->getName());
        
        return [
            'name' => $propertyName,
            'type' => '\Dvsa\Olcs\Api\Entity\User\User',
            'docBlock' => $column->getName() === 'created_by' ? 'Created by' : 'Last modified by',
            'defaultValue' => 'null',
            'nullable' => true,
            'isRelationship' => true,
        ];
    }

    public function getPriority(): int
    {
        return 80; // Higher priority than RelationshipTypeHandler
    }

    public function getRequiredImports(): array
    {
        return [
            'Doctrine\ORM\Mapping as ORM',
            'Gedmo\Mapping\Annotation as Gedmo'
        ];
    }
}