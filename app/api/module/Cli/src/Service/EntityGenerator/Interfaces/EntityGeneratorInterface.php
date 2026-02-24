<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces;

/**
 * Interface for entity generation
 */
interface EntityGeneratorInterface
{
    /**
     * Generate entities from table metadata
     *
     * @param array<TableMetadata> $tables Array of table metadata
     * @param array $config Configuration options
     * @return GenerationResult
     */
    public function generateEntities(array $tables, array $config): GenerationResult;

    /**
     * Generate a single entity from table metadata
     *
     * @param TableMetadata $table Table metadata
     * @param array $config Configuration options
     * @return EntityData
     */
    public function generateEntity(TableMetadata $table, array $config): EntityData;
}
