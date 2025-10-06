<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\EntityGenerator\Interfaces;

/**
 * Registry for type handlers
 */
interface TypeHandlerRegistryInterface
{
    /**
     * Register a type handler
     *
     * @param TypeHandlerInterface $handler
     */
    public function register(TypeHandlerInterface $handler): void;

    /**
     * Get the appropriate handler for a column
     *
     * @param ColumnMetadata $column
     * @param array $config
     * @return TypeHandlerInterface|null
     */
    public function getHandler(ColumnMetadata $column, array $config = []): ?TypeHandlerInterface;

    /**
     * Get all registered handlers
     *
     * @return array<TypeHandlerInterface>
     */
    public function getHandlers(): array;
}