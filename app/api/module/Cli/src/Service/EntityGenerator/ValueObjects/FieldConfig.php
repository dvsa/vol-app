<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\EntityGenerator\ValueObjects;

use Dvsa\Olcs\Cli\Service\EntityGenerator\Enums\CustomFieldType;

/**
 * Immutable field configuration from EntityConfig
 */
readonly class FieldConfig
{
    public function __construct(
        public CustomFieldType|null $type = null,
        public InversedByConfig|null $inversedBy = null,
        public array $cascade = [],
        public string|null $property = null,
        public string|null $fetch = null,
        public bool $translatable = false,
        public string|null $indexBy = null,
        public bool $orphanRemoval = false,
        public array $orderBy = []
    ) {}

    /**
     * Create from EntityConfig array data
     */
    public static function fromArray(array $config): self
    {
        return new self(
            type: isset($config['type']) ? CustomFieldType::from($config['type']) : null,
            inversedBy: isset($config['inversedBy']) ? InversedByConfig::fromArray($config['inversedBy']) : null,
            cascade: $config['cascade'] ?? [],
            property: $config['property'] ?? null,
            fetch: $config['fetch'] ?? null,
            translatable: $config['translatable'] ?? false,
            indexBy: $config['indexBy'] ?? null,
            orphanRemoval: $config['orphanRemoval'] ?? false,
            orderBy: $config['orderBy'] ?? []
        );
    }

    /**
     * Check if this field has custom configuration
     */
    public function hasCustomizations(): bool
    {
        return $this->type !== null 
            || $this->inversedBy !== null 
            || !empty($this->cascade)
            || $this->property !== null
            || $this->fetch !== null
            || $this->translatable
            || $this->indexBy !== null
            || $this->orphanRemoval
            || !empty($this->orderBy);
    }

    /**
     * Get effective field type, falling back to detected type
     */
    public function getEffectiveType(CustomFieldType $detectedType): CustomFieldType
    {
        return $this->type ?? $detectedType;
    }
}