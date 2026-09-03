<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\EntityGenerator\TypeHandlers;

use Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces\ColumnMetadata;

/**
 * Type handler for Gedmo Blameable fields (created_by, last_modified_by)
 */
class BlameableTypeHandler extends AbstractTypeHandler
{
    #[\Override]
    public function supports(ColumnMetadata $column, array $config = []): bool
    {
        return in_array($column->getName(), ['created_by', 'last_modified_by']);
    }

    #[\Override]
    public function generateAnnotation(ColumnMetadata $column, array $config = []): string
    {
        $annotations = [];

        // Add JoinColumn. JoinColumn's default is nullable: true, the opposite of Column's,
        // so a NOT NULL created_by has to say so explicitly - hardcoding true here made the
        // metadata misreport the constraint on every Blameable column that is NOT NULL.
        $annotations[] = sprintf(
            "#[ORM\JoinColumn(name: '%s', referencedColumnName: 'id', nullable: %s)]",
            $column->getName(),
            $column->isNullable() ? 'true' : 'false'
        );

        // Add ManyToOne relationship
        $annotations[] = '#[ORM\ManyToOne(targetEntity: \Dvsa\Olcs\Api\Entity\User\User::class, fetch: \'LAZY\')]';

        // Add Blameable annotation
        if ($column->getName() === 'created_by') {
            $annotations[] = "#[Gedmo\Blameable(on: 'create')]";
        } elseif ($column->getName() === 'last_modified_by') {
            $annotations[] = "#[Gedmo\Blameable(on: 'update')]";
        }

        return implode("\n    ", $annotations);
    }

    #[\Override]
    public function generateProperty(ColumnMetadata $column, array $config = []): array
    {
        $propertyName = $this->generatePropertyName($column->getName());

        return [
            'name' => $propertyName,
            'type' => '\\' . \Dvsa\Olcs\Api\Entity\User\User::class,
            'docBlock' => $column->getName() === 'created_by' ? 'Created by' : 'Last modified by',
            'defaultValue' => 'null',
            'nullable' => $column->isNullable(),
            'isRelationship' => true,
        ];
    }

    #[\Override]
    public function getPriority(): int
    {
        return 80; // Higher priority than RelationshipTypeHandler
    }

    #[\Override]
    public function getRequiredImports(): array
    {
        return [
            'Doctrine\ORM\Mapping as ORM',
            'Gedmo\Mapping\Annotation as Gedmo'
        ];
    }
}
