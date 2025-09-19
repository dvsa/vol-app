<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\EntityGenerator;

use WeakMap;
use Dvsa\Olcs\Cli\Service\EntityGenerator\ValueObjects\FieldConfig;
use Dvsa\Olcs\Cli\Service\EntityGenerator\Enums\CustomFieldType;
use Dvsa\Olcs\Cli\Service\EntityGenerator\MethodGeneratorService;

/**
 * Enhanced template renderer with PHP 8.2 features and collection support
 */
class TemplateRenderer
{
    private readonly string $templatePath;
    private WeakMap $templateCache;

    public function __construct(
        string $templatePath,
        private readonly MethodGeneratorService $methodGenerator
    ) {
        $this->templatePath = rtrim($templatePath, '/');
        $this->templateCache = new WeakMap();
    }

    /**
     * Render a template with the given data
     */
    public function render(string $templateName, array $data = []): string
    {
        $templateFile = $this->templatePath . '/' . $templateName . '.phtml';

        if (!file_exists($templateFile)) {
            throw new \InvalidArgumentException(sprintf('Template file not found: %s', $templateFile));
        }

        // Make MethodGeneratorService methods available to template as helper functions
        $methodGenerator = $this->methodGenerator;

        // Extract data to variables
        extract($data, EXTR_SKIP);

        // Start output buffering
        ob_start();

        // Include the template
        include $templateFile;

        // Get the output and clean the buffer
        $content = ob_get_clean();

        if ($content === false) {
            throw new \RuntimeException(sprintf('Failed to render template: %s', $templateName));
        }

        return $content;
    }

    /**
     * Helper method to generate options from attributes (for annotations)
     */
    public function generateOptionsFromAttributes(array $attributes, string $type): string
    {
        $options = [];

        foreach ($attributes as $key => $value) {
            // Skip 'unique' attribute for indexes (it's not valid for @ORM\Index)
            if ($type === 'indexes' && $key === 'unique') {
                continue;
            }

            if ($key === 'options' && is_array($value)) {
                // Handle nested options array separately
                continue;
            }

            if (is_array($value)) {
                // Skip empty arrays in annotations
                if (empty($value)) {
                    continue;
                }

                // Handle arrays of strings (like columns)
                $cleanValues = array_filter(array_map(function($v) {
                    if (is_array($v)) {
                        // Skip nested arrays
                        return null;
                    }
                    return '"' . $v . '"';
                }, $value));

                if (!empty($cleanValues)) {
                    $options[] = $key . '={' . implode(', ', $cleanValues) . '}';
                }
            } elseif ($value !== null && $value !== '') {
                // Skip empty string values
                if ($value === '') {
                    continue;
                }
                $options[] = $key . '="' . $value . '"';
            }
        }

        return implode(', ', $options);
    }

    /**
     * Helper method to check if a property is from a trait
     */
    public function isPropertyFromTrait(array $fieldData): bool
    {
        $traitProperties = [
            'id', 'createdOn', 'createdBy', 'lastModifiedOn', 'lastModifiedBy', 'deletedDate', 'version'
        ];

        return in_array($fieldData['property']['name'], $traitProperties);
    }

    /**
     * Enhanced helper to determine trait usage
     */
    public function getRequiredTraits(array $fields, bool $hasCollections, bool $softDeletable): array
    {
        $traits = ['BundleSerializableTrait', 'ProcessDateTrait'];

        // Use match expression for cleaner logic
        $traits[] = match ($hasCollections) {
            true => 'ClearPropertiesWithCollectionsTrait',
            false => 'ClearPropertiesTrait'
        };

        // Check for specific trait fields
        foreach ($fields as $field) {
            $propertyName = $field['property']['name'] ?? '';

            match ($propertyName) {
                'createdOn' => $traits[] = 'CreatedOnTrait',
                'lastModifiedOn' => $traits[] = 'ModifiedOnTrait',
                default => null
            };
        }

        if ($softDeletable) {
            $traits[] = 'SoftDeletableTrait';
        }

        return array_unique($traits);
    }

    /**
     * Generate collection initialization code
     */
    public function generateCollectionInitialization(array $collections): string
    {
        if (empty($collections)) {
            return '';
        }

        $initCode = [];
        foreach ($collections as $collection) {
            $propertyName = $collection['property'] ?? $collection['field'] ?? '';
            if (!empty($propertyName)) {
                $initCode[] = sprintf('        $this->%s = new ArrayCollection();', $propertyName);
            }
        }

        return implode("\n", $initCode);
    }

    /**
     * Check if entity should implement specific interfaces
     */
    public function shouldImplementInterface(string $interfaceName, array $entityData): bool
    {
        return match ($interfaceName) {
            'BundleSerializableInterface' => true, // All entities implement this
            'JsonSerializable' => true, // All entities implement this
            'ContextProviderInterface' => $this->hasContextProviderMethods($entityData),
            'OrganisationProviderInterface' => $this->hasOrganisationProviderMethods($entityData),
            default => false
        };
    }

    /**
     * Generate import statements with proper organization
     */
    public function generateImports(array $fields, bool $hasCollections, bool $softDeletable): array
    {
        $imports = [
            'Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface',
            'JsonSerializable',
            'Doctrine\ORM\Mapping as ORM'
        ];

        // Add trait imports
        $traits = $this->getRequiredTraits($fields, $hasCollections, $softDeletable);
        foreach ($traits as $trait) {
            $imports[] = sprintf('Dvsa\Olcs\Api\Entity\Traits\%s', $trait);
        }

        // Add collection imports if needed
        if ($hasCollections) {
            $imports[] = 'Doctrine\Common\Collections\ArrayCollection';
            $imports[] = 'Doctrine\Common\Collections\Collection';
        }

        // Add Gedmo import if needed
        if ($softDeletable || $this->hasBlameableFields($fields)) {
            $imports[] = 'Gedmo\Mapping\Annotation as Gedmo';
        }

        return array_unique($imports);
    }

    /**
     * Check if entity has blameable fields
     */
    private function hasBlameableFields(array $fields): bool
    {
        return array_reduce($fields, function (bool $carry, array $field): bool {
            $propertyName = $field['property']['name'] ?? '';
            return $carry || in_array($propertyName, ['createdBy', 'lastModifiedBy']);
        }, false);
    }

    /**
     * Check for context provider methods (placeholder)
     */
    private function hasContextProviderMethods(array $entityData): bool
    {
        // This would check for specific fields or methods that indicate context provider capability
        return false;
    }

    /**
     * Check for organisation provider methods (placeholder)
     */
    private function hasOrganisationProviderMethods(array $entityData): bool
    {
        // This would check for organisation-related fields
        return false;
    }

    /**
     * Helper method to generate property getter/setter methods
     */
    public function generateMethods(array $fieldData): string
    {
        $property = $fieldData['property'];
        $propertyName = $property['name'];
        $type = $property['type'];

        $getterName = 'get' . ucfirst($propertyName);
        $setterName = 'set' . ucfirst($propertyName);

        $methods = [];

        // Getter
        $methods[] = sprintf(
            "    /**\n     * Get %s\n     *\n     * @return %s\n     */\n    public function %s(): %s\n    {\n        return \$this->%s;\n    }",
            $property['docBlock'],
            $type,
            $getterName,
            $property['nullable'] ? '?' . $type : $type,
            $propertyName
        );

        // Setter
        $methods[] = sprintf(
            "    /**\n     * Set %s\n     *\n     * @param %s \$%s\n     * @return \$this\n     */\n    public function %s(%s \$%s): self\n    {\n        \$this->%s = \$%s;\n        return \$this;\n    }",
            $property['docBlock'],
            $type,
            $propertyName,
            $setterName,
            $property['nullable'] ? '?' . $type : $type,
            $propertyName,
            $propertyName,
            $propertyName
        );

        return implode("\n\n", $methods);
    }
}
