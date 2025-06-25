<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces;

/**
 * Interface for database schema introspection
 * 
 * Abstracts the database schema reading process to make it swappable
 * across different Doctrine versions or even different ORMs
 */
interface SchemaIntrospectorInterface
{
    /**
     * Get all table names from the database
     *
     * @return array<string> Array of table names
     */
    public function getTableNames(): array;

    /**
     * Get detailed metadata for a specific table
     *
     * @param string $tableName The table name
     * @return TableMetadata Table metadata object
     */
    public function getTableMetadata(string $tableName): TableMetadata;

    /**
     * Get all foreign key relationships in the database
     *
     * @return array<string, array> Array of relationships indexed by table name
     */
    public function getRelationships(): array;

    /**
     * Check if a table should be ignored based on configuration
     *
     * @param string $tableName The table name
     * @return bool True if table should be ignored
     */
    public function shouldIgnoreTable(string $tableName): bool;
}