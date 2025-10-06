<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces;

/**
 * Value object representing generated entity data
 */
class EntityData
{
    private string $tableName;
    private string $className;
    private string $namespace;
    private string $namespaceFolder;
    private string $abstractContent;
    private string $concreteContent;
    private string $testContent;
    private string $mappingContent;
    private array $metadata;

    public function __construct(
        string $tableName,
        string $className,
        string $namespace,
        string $namespaceFolder = '',
        string $abstractContent = '',
        string $concreteContent = '',
        string $testContent = '',
        string $mappingContent = '',
        array $metadata = []
    ) {
        $this->tableName = $tableName;
        $this->className = $className;
        $this->namespace = $namespace;
        $this->namespaceFolder = $namespaceFolder ?: $this->extractNamespaceFolder($namespace);
        $this->abstractContent = $abstractContent;
        $this->concreteContent = $concreteContent;
        $this->testContent = $testContent;
        $this->mappingContent = $mappingContent;
        $this->metadata = $metadata;
    }

    private function extractNamespaceFolder(string $namespace): string
    {
        // Extract the last part of the namespace as the folder
        $parts = explode('\\', $namespace);
        return array_pop($parts) ?? 'Generic';
    }

    public function getTableName(): string
    {
        return $this->tableName;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function getAbstractContent(): string
    {
        return $this->abstractContent;
    }

    public function setAbstractContent(string $content): void
    {
        $this->abstractContent = $content;
    }

    public function getConcreteContent(): string
    {
        return $this->concreteContent;
    }

    public function setConcreteContent(string $content): void
    {
        $this->concreteContent = $content;
    }

    public function getTestContent(): string
    {
        return $this->testContent;
    }

    public function setTestContent(string $content): void
    {
        $this->testContent = $content;
    }

    public function getMappingContent(): string
    {
        return $this->mappingContent;
    }

    public function setMappingContent(string $content): void
    {
        $this->mappingContent = $content;
    }

    public function getMetadata(): array
    {
        return $this->metadata;
    }

    public function setMetadata(array $metadata): void
    {
        $this->metadata = $metadata;
    }

    public function getAbstractClassName(): string
    {
        return 'Abstract' . $this->className;
    }

    public function getFullClassName(): string
    {
        return $this->namespace . '\\' . $this->className;
    }

    public function getFullAbstractClassName(): string
    {
        return $this->namespace . '\\' . $this->getAbstractClassName();
    }

    public function getNamespaceFolder(): string
    {
        return $this->namespaceFolder;
    }

    public function setNamespaceFolder(string $namespaceFolder): void
    {
        $this->namespaceFolder = $namespaceFolder;
    }
}