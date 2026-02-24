<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\EntityGenerator\ValueObjects;

/**
 * Immutable inversedBy configuration
 */
readonly class InversedByConfig
{
    public function __construct(
        public string $entity,
        public string $property,
        public array $cascade = [],
        public string|null $indexBy = null,
        public bool $orphanRemoval = false,
        public array $orderBy = [],
        public string|null $fetch = null
    ) {
    }

    /**
     * Create from EntityConfig array data
     */
    public static function fromArray(array $config): self
    {
        return new self(
            entity: $config['entity'],
            property: $config['property'],
            cascade: $config['cascade'] ?? [],
            indexBy: $config['indexBy'] ?? null,
            orphanRemoval: self::toBool($config['orphanRemoval'] ?? false),
            orderBy: $config['orderBy'] ?? [],
            fetch: $config['fetch'] ?? null
        );
    }

    /**
     * Convert value to boolean
     */
    private static function toBool($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            return strtolower($value) === 'true' || $value === '1';
        }

        return (bool) $value;
    }

    /**
     * Get the full entity namespace
     */
    public function getFullEntityName(string $baseNamespace): string
    {
        return $baseNamespace . '\\' . $this->entity;
    }
}
