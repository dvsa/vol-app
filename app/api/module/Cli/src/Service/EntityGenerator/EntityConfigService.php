<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\EntityGenerator;

use Dvsa\Olcs\Cli\Service\EntityGenerator\ValueObjects\FieldConfig;
use Dvsa\Olcs\Cli\Service\EntityGenerator\Exceptions\EntityConfigException;

/**
 * Service for loading and processing EntityConfig.php
 */
final class EntityConfigService
{
    private readonly array $config;
    private readonly array $namespaces;
    private array $tableConfigCache = [];

    public function __construct(
        #[\SensitiveParameter] private readonly string $configPath
    ) {
        $this->config = $this->loadConfig();
        $this->namespaces = $this->config['namespaces'] ?? [];
    }

    /**
     * Get field configuration for a specific table and column
     */
    public function getFieldConfig(string $table, string $column): FieldConfig|null
    {
        $tableConfig = $this->config[$table] ?? [];
        $fieldConfig = $tableConfig[$column] ?? [];

        if (empty($fieldConfig)) {
            return null;
        }

        return FieldConfig::fromArray($fieldConfig);
    }

    /**
     * Get all field configurations for a table with caching
     */
    public function getTableConfig(string $table): array
    {
        if (!isset($this->tableConfigCache[$table])) {
            $tableConfig = $this->config[$table] ?? [];
            $result = [];

            foreach ($tableConfig as $column => $config) {
                $result[$column] = FieldConfig::fromArray($config);
            }

            $this->tableConfigCache[$table] = $result;
        }

        return $this->tableConfigCache[$table];
    }

    /**
     * Get namespace for an entity
     */
    public function getEntityNamespace(string $entityName): string|null
    {
        return $this->namespaces[$entityName] ?? null;
    }

    /**
     * Get mapping configuration
     */
    public function getMappingConfig(): array
    {
        return $this->config['mappingConfig'] ?? [];
    }

    /**
     * Get class name override for table
     */
    public function getClassNameForTable(string $tableName): string|null
    {
        $mappingConfig = $this->getMappingConfig();
        return $mappingConfig['classNameForTable'][$tableName] ?? null;
    }

    /**
     * Get field name override for column
     */
    public function getFieldNameForColumn(string $tableName, string $columnName): string|null
    {
        $mappingConfig = $this->getMappingConfig();
        return $mappingConfig['fieldNameForColumn'][$tableName][$columnName] ?? null;
    }

    /**
     * Get metadata for a namespace (e.g., skipManyToMany)
     */
    public function getNamespaceMetadata(string $namespace): array
    {
        $mappingConfig = $this->getMappingConfig();
        return $mappingConfig[$namespace]['metadata'] ?? [];
    }

    /**
     * Check if ManyToMany relationships should be skipped for a namespace
     */
    public function shouldSkipManyToMany(string $namespace): bool
    {
        $metadata = $this->getNamespaceMetadata($namespace);
        return $metadata['skipManyToMany'] ?? false;
    }

    /**
     * Check if table should be ignored
     */
    public function shouldIgnoreTable(string $tableName): bool
    {
        // Skip history tables, liquibase tables, and data retention tables
        $ignorePatterns = [
            '/_hist$/',
            '/^DATABASECHANGE/',
            '/^log_update$/',
            '/^DR_/',
        ];

        foreach ($ignorePatterns as $pattern) {
            if (preg_match($pattern, $tableName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get all inverse relationships that should be generated
     */
    public function getInverseRelationships(): array
    {
        $inverseRelationships = [];

        foreach ($this->config as $tableName => $tableConfig) {
            if ($tableName === 'mappingConfig' || $tableName === 'namespaces') {
                continue;
            }

            foreach ($tableConfig as $columnName => $fieldConfig) {
                if (isset($fieldConfig['inversedBy'])) {
                    $inversedByConfig = $fieldConfig['inversedBy'];
                    $targetEntity = $inversedByConfig['entity'];

                    $inverseRelationships[$targetEntity] ??= [];
                    $inverseRelationships[$targetEntity][] = [
                        'sourceTable' => $tableName,
                        'sourceColumn' => $columnName,
                        'property' => $inversedByConfig['property'],
                        'config' => FieldConfig::fromArray($fieldConfig)
                    ];
                }
            }
        }

        return $inverseRelationships;
    }

    /**
     * Load and validate the EntityConfig.php file
     */
    private function loadConfig(): array
    {
        if (!file_exists($this->configPath)) {
            throw new EntityConfigException(
                "EntityConfig file not found at: {$this->configPath}"
            );
        }

        $config = include $this->configPath;

        if (!is_array($config)) {
            throw new EntityConfigException(
                "EntityConfig must return an array"
            );
        }

        return $config;
    }
}
