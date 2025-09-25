<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces;

/**
 * Value object representing table metadata
 */
class TableMetadata
{
    private string $tableName;
    private array $columns;
    private array $indexes;
    private array $uniqueConstraints;
    private array $foreignKeys;
    private ?string $comment;

    public function __construct(
        string $tableName,
        array $columns,
        array $indexes = [],
        array $uniqueConstraints = [],
        array $foreignKeys = [],
        ?string $comment = null
    ) {
        $this->tableName = $tableName;
        $this->columns = $columns;
        $this->indexes = $indexes;
        $this->uniqueConstraints = $uniqueConstraints;
        $this->foreignKeys = $foreignKeys;
        $this->comment = $comment;
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function getColumn(string $columnName): ?ColumnMetadata
    {
        return $this->columns[$columnName] ?? null;
    }

    public function getIndexes(): array
    {
        return $this->indexes;
    }

    public function getUniqueConstraints(): array
    {
        return $this->uniqueConstraints;
    }

    public function getForeignKeys(): array
    {
        return $this->foreignKeys;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    /**
     * Get primary key column names
     */
    public function getPrimaryKeyColumns(): array
    {
        return array_filter(
            array_keys($this->columns),
            fn($columnName) => $this->columns[$columnName]->isPrimary()
        );
    }
}