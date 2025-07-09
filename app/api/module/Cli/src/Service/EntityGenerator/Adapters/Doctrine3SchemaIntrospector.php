<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\EntityGenerator\Adapters;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\ForeignKeyConstraint;
use Doctrine\DBAL\Schema\Index;
use Doctrine\DBAL\Schema\Table;
use Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces\ColumnMetadata;
use Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces\SchemaIntrospectorInterface;
use Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces\TableMetadata;

/**
 * Doctrine 3.x implementation of schema introspection
 */
class Doctrine3SchemaIntrospector implements SchemaIntrospectorInterface
{
    private Connection $connection;
    private AbstractSchemaManager $schemaManager;
    private array $config;

    public function __construct(Connection $connection, array $config = [])
    {
        $this->connection = $connection;
        $this->schemaManager = $connection->createSchemaManager();
        $this->config = $config;
    }

    public function getTableNames(): array
    {
        $tables = $this->schemaManager->listTableNames();
        
        return array_filter($tables, fn($table) => !$this->shouldIgnoreTable($table));
    }

    public function getTableMetadata(string $tableName): TableMetadata
    {
        $table = $this->schemaManager->introspectTable($tableName);
        
        return new TableMetadata(
            $tableName,
            $this->extractColumns($table),
            $this->extractIndexes($table),
            $this->extractUniqueConstraints($table),
            $this->extractForeignKeys($table),
            $table->getComment()
        );
    }

    public function getRelationships(): array
    {
        $relationships = [];
        $tableNames = $this->getTableNames();

        foreach ($tableNames as $tableName) {
            $table = $this->schemaManager->introspectTable($tableName);
            $foreignKeys = $table->getForeignKeys();

            foreach ($foreignKeys as $foreignKey) {
                $relationships[$tableName][] = [
                    'type' => 'many_to_one',
                    'local_columns' => $foreignKey->getLocalColumns(),
                    'foreign_table' => $foreignKey->getForeignTableName(),
                    'foreign_columns' => $foreignKey->getForeignColumns(),
                    'name' => $foreignKey->getName(),
                    'on_delete' => $foreignKey->onDelete(),
                    'on_update' => $foreignKey->onUpdate(),
                ];
            }
        }

        // Add inverse relationships (one_to_many)
        foreach ($relationships as $tableName => $tableRelationships) {
            foreach ($tableRelationships as $relationship) {
                $foreignTable = $relationship['foreign_table'];
                if (!isset($relationships[$foreignTable])) {
                    $relationships[$foreignTable] = [];
                }

                $relationships[$foreignTable][] = [
                    'type' => 'one_to_many',
                    'local_columns' => $relationship['foreign_columns'],
                    'foreign_table' => $tableName,
                    'foreign_columns' => $relationship['local_columns'],
                    'name' => $relationship['name'] . '_inverse',
                    'mapped_by' => $this->getPropertyNameFromColumn($relationship['local_columns'][0]),
                ];
            }
        }

        // Add many_to_many relationships from join tables
        $joinTables = $this->detectJoinTables();
        foreach ($joinTables as $joinTableName => $joinTableInfo) {
            // Only add ManyToMany relationship to the FIRST entity (owner of the relationship)
            // The inverse side will only be added if explicitly configured in EntityConfig
            $owningEntity = $joinTableInfo['entities'][0];
            $inverseEntity = $joinTableInfo['entities'][1];
            
            if (!isset($relationships[$owningEntity['table']])) {
                $relationships[$owningEntity['table']] = [];
            }
            
            $relationships[$owningEntity['table']][] = [
                'type' => 'many_to_many',
                'join_table' => $joinTableName,
                'local_columns' => $owningEntity['columns'],
                'foreign_table' => $inverseEntity['table'],
                'foreign_columns' => $inverseEntity['columns'],
                'join_columns' => $owningEntity['join_columns'],
                'inverse_join_columns' => $inverseEntity['join_columns'],
                'name' => $joinTableName . '_' . $owningEntity['table'] . '_' . $inverseEntity['table'],
            ];
        }

        return $relationships;
    }

    public function shouldIgnoreTable(string $tableName): bool
    {
        $ignoredPatterns = $this->config['ignored_tables'] ?? [
            '/.*_hist$/',           // History tables
            '/^DATABASECHANGELOG/',  // Liquibase tables
            '/^DR_.*/',             // Data retention tables
            '/^log_update$/',       // Log tables
        ];

        foreach ($ignoredPatterns as $pattern) {
            if (preg_match($pattern, $tableName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Extract column metadata from Doctrine table
     */
    private function extractColumns(Table $table): array
    {
        $columns = [];
        
        // Get primary key columns
        $primaryKeyColumns = [];
        if ($table->hasPrimaryKey()) {
            $primaryKey = $table->getPrimaryKey();
            $primaryKeyColumns = $primaryKey->getColumns();
        }

        foreach ($table->getColumns() as $column) {
            $columnMetadata = $this->convertColumn($column);
            // Set primary key flag
            if (in_array($column->getName(), $primaryKeyColumns)) {
                $columnMetadata = new ColumnMetadata(
                    name: $columnMetadata->getName(),
                    type: $columnMetadata->getType(),
                    length: $columnMetadata->getLength(),
                    nullable: $columnMetadata->isNullable(),
                    primary: true,
                    autoIncrement: $columnMetadata->isAutoIncrement(),
                    default: $columnMetadata->getDefault(),
                    comment: $columnMetadata->getComment(),
                    options: $columnMetadata->getOptions()
                );
            }
            $columns[$column->getName()] = $columnMetadata;
        }

        return $columns;
    }

    /**
     * Convert Doctrine Column to our ColumnMetadata
     */
    private function convertColumn(Column $column): ColumnMetadata
    {
        return new ColumnMetadata(
            name: $column->getName(),
            type: $column->getType()->getName(),
            length: $column->getLength(),
            nullable: !$column->getNotnull(),
            primary: false, // Will be set later from primary key info
            autoIncrement: $column->getAutoincrement(),
            default: $column->getDefault(),
            comment: $column->getComment(),
            options: $column->getCustomSchemaOptions()
        );
    }

    /**
     * Extract index information
     */
    private function extractIndexes(Table $table): array
    {
        $indexes = [];

        foreach ($table->getIndexes() as $index) {
            if ($index->isPrimary()) {
                continue; // Skip primary key index
            }

            $indexes[] = [
                'name' => $index->getName(),
                'columns' => $index->getColumns(),
                'unique' => $index->isUnique(),
                'flags' => $index->getFlags(),
                'options' => $index->getOptions(),
            ];
        }

        return $indexes;
    }

    /**
     * Extract unique constraints
     */
    private function extractUniqueConstraints(Table $table): array
    {
        $constraints = [];

        foreach ($table->getIndexes() as $index) {
            if ($index->isUnique() && !$index->isPrimary()) {
                $constraints[] = [
                    'name' => $index->getName(),
                    'columns' => $index->getColumns(),
                ];
            }
        }

        return $constraints;
    }

    /**
     * Extract foreign key information
     */
    private function extractForeignKeys(Table $table): array
    {
        $foreignKeys = [];

        foreach ($table->getForeignKeys() as $foreignKey) {
            $foreignKeys[] = [
                'name' => $foreignKey->getName(),
                'local_columns' => $foreignKey->getLocalColumns(),
                'foreign_table' => $foreignKey->getForeignTableName(),
                'foreign_columns' => $foreignKey->getForeignColumns(),
                'on_delete' => $foreignKey->onDelete(),
                'on_update' => $foreignKey->onUpdate(),
            ];
        }

        return $foreignKeys;
    }

    /**
     * Detect join tables (tables with exactly 2 foreign keys that form the primary key)
     */
    private function detectJoinTables(): array
    {
        $joinTables = [];
        $tableNames = $this->getTableNames();

        foreach ($tableNames as $tableName) {
            if ($this->shouldIgnoreTable($tableName)) {
                continue;
            }

            $table = $this->schemaManager->introspectTable($tableName);
            $foreignKeys = $table->getForeignKeys();
            $primaryKey = $table->getPrimaryKey();

            // A join table should have exactly 2 foreign keys
            if (count($foreignKeys) !== 2) {
                continue;
            }

            // The primary key should consist of exactly the foreign key columns
            if (!$primaryKey) {
                continue;
            }

            $primaryKeyColumns = $primaryKey->getColumns();
            $allForeignKeyColumns = [];
            
            foreach ($foreignKeys as $fk) {
                $allForeignKeyColumns = array_merge($allForeignKeyColumns, $fk->getLocalColumns());
            }

            // Check if primary key columns match foreign key columns
            sort($primaryKeyColumns);
            sort($allForeignKeyColumns);
            
            if ($primaryKeyColumns !== $allForeignKeyColumns) {
                continue;
            }

            // Check that the table has no other columns (only the 2 FK columns)
            $allColumns = $table->getColumns();
            if (count($allColumns) !== count($allForeignKeyColumns)) {
                continue;
            }

            // This is a join table
            $entities = [];
            foreach ($foreignKeys as $fk) {
                $entities[] = [
                    'table' => $fk->getForeignTableName(),
                    'columns' => $fk->getForeignColumns(),
                    'join_columns' => $fk->getLocalColumns(),
                    'fk_name' => $fk->getName(),
                ];
            }

            $joinTables[$tableName] = [
                'table' => $tableName,
                'entities' => $entities,
            ];
        }

        return $joinTables;
    }

    /**
     * Convert column name to property name (snake_case to camelCase)
     */
    private function getPropertyNameFromColumn(string $columnName): string
    {
        // Remove _id suffix if present
        $propertyName = preg_replace('/_id$/', '', $columnName);
        
        // Convert to camelCase
        return lcfirst(str_replace('_', '', ucwords($propertyName, '_')));
    }
}