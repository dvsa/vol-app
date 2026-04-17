<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces;

/**
 * Interface for custom type handlers
 *
 * Each custom type (YesNo, Encrypted, etc.) implements this interface
 * to provide specific handling for annotation generation and property creation
 */
interface TypeHandlerInterface
{
    /**
     * Check if this handler supports the given column
     *
     * @param ColumnMetadata $column The column metadata
     * @param array $config Custom configuration from EntityConfig.php
     * @return bool True if this handler should process this column
     */
    public function supports(ColumnMetadata $column, array $config = []): bool;

    /**
     * Generate the Doctrine annotation for this column
     *
     * @param ColumnMetadata $column The column metadata
     * @param array $config Custom configuration from EntityConfig.php
     * @return string The generated annotation
     */
    public function generateAnnotation(ColumnMetadata $column, array $config = []): string;

    /**
     * Generate the property declaration for this column
     *
     * @param ColumnMetadata $column The column metadata
     * @param array $config Custom configuration from EntityConfig.php
     * @return array Property data [name, type, docBlock, defaultValue]
     */
    public function generateProperty(ColumnMetadata $column, array $config = []): array;

    /**
     * Get required imports for this type handler
     *
     * @return array<string> Array of class names to import
     */
    public function getRequiredImports(): array;

    /**
     * Get the priority of this handler (higher = processed first)
     *
     * @return int Priority value
     */
    public function getPriority(): int;
}
