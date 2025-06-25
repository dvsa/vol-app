<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\EntityGenerator;

use Laminas\Filter\Word\CamelCaseToSeparator;
use Doctrine\Inflector\InflectorFactory;

/**
 * Method Generator Service
 * 
 * Provides helper methods for generating entity getter/setter methods
 * Based on the original AlignEntitiesToSchema script functionality
 */
final readonly class MethodGeneratorService
{
    private CamelCaseToSeparator $camelCaseFilter;
    private \Doctrine\Inflector\Inflector $inflector;

    public function __construct()
    {
        $this->camelCaseFilter = new CamelCaseToSeparator(' ');
        $this->inflector = InflectorFactory::create()->build();
    }

    /**
     * Format a property name from field configuration
     * Uses the actual property name without modification
     */
    public function formatPropertyName(array $field): string
    {
        // For inverse relationships, the property name is already set correctly
        if (isset($field['isInverse']) && $field['isInverse'] === true) {
            return $field['property']['name'] ?? '';
        }
        
        // For regular fields, just return the property name as-is
        return $field['property']['name'] ?? '';
    }

    /**
     * Format parameter name for method signatures
     * Uses 'input' for overly long property names
     */
    public function formatParamName(array $field): string
    {
        $property = $this->formatPropertyName($field);

        if (strlen($property) >= 40) {
            return 'input';
        }

        return $property;
    }

    /**
     * Convert camelCase names to readable strings with spaces
     */
    public function getReadableStringFromName(string $name): string
    {
        return $this->camelCaseFilter->filter($name);
    }

    /**
     * Map Doctrine types to PHP types for PHPDoc annotations
     */
    public function getPhpTypeFromType(string $type): string
    {
        // Handle array types for collections
        if (str_starts_with($type, 'ArrayCollection') || str_starts_with($type, '\\Doctrine\\Common\\Collections\\ArrayCollection')) {
            return '\\Doctrine\\Common\\Collections\\ArrayCollection';
        }
        
        // Handle entity types (anything starting with uppercase or backslash)
        if (preg_match('/^[A-Z\\\\]/', $type)) {
            // Ensure full namespace for entity types
            if (!str_contains($type, '\\')) {
                return '\\Dvsa\\Olcs\\Api\\Entity\\' . $type;
            }
            return str_starts_with($type, '\\') ? $type : '\\' . $type;
        }
        
        return match (strtolower($type)) {
            'string', 'varchar', 'char' => 'string',
            'boolean', 'bool' => 'bool',
            'text', 'yesno', 'yesnonull', 'encrypted_string', 'json' => 'string',
            'bigint', 'integer', 'smallint', 'int' => 'int',
            'datetime', 'date', 'time', 'timestamp' => '\\DateTime',
            'decimal', 'float', 'double' => 'float',
            'array' => 'array',
            default => 'mixed'
        };
    }

    /**
     * Determine if a property is defined in a trait rather than the entity
     */
    public function isPropertyFromTrait(array $field): bool
    {
        $propertyName = $field['property']['name'] ?? '';
        
        return in_array($propertyName, [
            'createdOn',      // Provided by CreatedOnTrait
            'lastModifiedOn', // Provided by ModifiedOnTrait
            'deletedDate'     // Provided by SoftDeletableTrait
        ]);
    }

    /**
     * Check if field is createdOn
     */
    public function isCreatedOnField(array $field): bool
    {
        return ($field['property']['name'] ?? '') === 'createdOn';
    }

    /**
     * Check if field is lastModifiedOn
     */
    public function isLastModifiedOnField(array $field): bool
    {
        return ($field['property']['name'] ?? '') === 'lastModifiedOn';
    }

    /**
     * Check if field is deletedDate
     */
    public function isDeletedDateField(array $field): bool
    {
        return ($field['property']['name'] ?? '') === 'deletedDate';
    }

    /**
     * Check if field is a created by field (for Gedmo Blameable)
     */
    public function isCreatedByField(array $field): bool
    {
        return ($field['property']['name'] ?? '') === 'createdBy';
    }

    /**
     * Check if field is a last modified by field (for Gedmo Blameable)
     */
    public function isLastModifiedByField(array $field): bool
    {
        return ($field['property']['name'] ?? '') === 'lastModifiedBy';
    }

    /**
     * Get the target entity class name for relationships
     */
    public function getTargetEntity(array $field): string
    {
        // Extract from annotation like @ORM\ManyToOne(targetEntity="Dvsa\Olcs\Api\Entity\User\User")
        $annotation = $field['annotation'] ?? '';
        if (preg_match('/targetEntity="([^"]+)"/', $annotation, $matches)) {
            return $matches[1];
        }
        
        // Fallback - extract from type hint in property
        $type = $field['property']['type'] ?? '';
        if (str_starts_with($type, '\\')) {
            return ltrim($type, '\\');
        }
        
        return 'unknown';
    }

    /**
     * Check if this is a DateTime field type
     */
    public function isDateTimeField(array $field): bool
    {
        $type = $field['property']['type'] ?? '';
        return in_array($type, ['\\DateTime', '\DateTime', 'DateTime']);
    }

    /**
     * Check if this is a collection field (oneToMany or manyToMany)
     */
    public function isCollectionField(array $field): bool
    {
        // Check both 'type' and 'relationship' keys since inverse relationships use 'relationship'
        $type = $field['type'] ?? $field['relationship'] ?? '';
        return in_array($type, ['oneToMany', 'manyToMany']);
    }

    /**
     * Check if this is a relationship field (oneToOne or manyToOne)
     */
    public function isRelationshipField(array $field): bool
    {
        // Check both 'type' and 'relationship' keys since inverse relationships use 'relationship'
        $type = $field['type'] ?? $field['relationship'] ?? '';
        return in_array($type, ['oneToOne', 'manyToOne']);
    }

    /**
     * Get the return type for fluent interface (usually the entity class name)
     */
    public function getFluidReturnType(string $className): string
    {
        // Remove "Abstract" prefix if present
        return str_replace('Abstract', '', $className);
    }

    /**
     * Pluralize a string using Doctrine Inflector
     */
    public function pluralize(string $word): string
    {
        return $this->inflector->pluralize($word);
    }

    /**
     * Singularize a string using Doctrine Inflector
     */
    public function singularize(string $word): string
    {
        return $this->inflector->singularize($word);
    }
}