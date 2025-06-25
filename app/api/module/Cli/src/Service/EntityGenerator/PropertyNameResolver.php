<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Cli\Service\EntityGenerator;

use Doctrine\Inflector\InflectorFactory;
use Doctrine\Inflector\Inflector;

/**
 * Property Name Resolver Service
 * 
 * Handles smart property naming including proper pluralization for collections
 * and respects existing naming conventions in the OLCS codebase
 */
final readonly class PropertyNameResolver
{
    private Inflector $inflector;
    
    /**
     * Known irregular plurals and naming exceptions in OLCS
     */
    private const PLURAL_OVERRIDES = [
        'rolePermission' => 'rolePermissions',
        'organisationPerson' => 'organisationPersons',
        'transportManagerLicence' => 'transportManagerLicences',
        'transportManagerApplication' => 'transportManagerApplications',
        'communityLicSuspension' => 'communityLicSuspensions',
        'communityLicWithdrawal' => 'communityLicWithdrawals',
        'communityLicSuspensionReason' => 'communityLicSuspensionReasons',
        'communityLicWithdrawalReason' => 'communityLicWithdrawalReasons',
        'address' => 'addresses',
        'bus' => 'buses',
        'status' => 'statuses',
    ];

    /**
     * Properties that should remain singular even for collections
     */
    private const SINGULAR_COLLECTIONS = [
        'operatingCentreTrafficArea',
        'correspondenceAddress',
        'establishmentAddress',
    ];

    public function __construct()
    {
        $this->inflector = InflectorFactory::create()->build();
    }

    /**
     * Resolve the property name for a field, handling pluralization for collections
     * 
     * @param string $basePropertyName The base property name from config/schema
     * @param bool $isCollection Whether this is a collection relationship
     * @param string|null $entityConfigProperty Override from EntityConfig if specified
     * @return string The resolved property name
     */
    public function resolvePropertyName(
        string $basePropertyName,
        bool $isCollection,
        ?string $entityConfigProperty = null
    ): string {
        // If EntityConfig specifies a property name, check if it needs pluralization
        if ($entityConfigProperty !== null) {
            $basePropertyName = $entityConfigProperty;
        }

        // If not a collection, return as-is
        if (!$isCollection) {
            return $basePropertyName;
        }

        // Check if this should remain singular
        if (in_array($basePropertyName, self::SINGULAR_COLLECTIONS)) {
            return $basePropertyName;
        }

        // Check for known overrides first
        if (isset(self::PLURAL_OVERRIDES[$basePropertyName])) {
            return self::PLURAL_OVERRIDES[$basePropertyName];
        }

        // If already ends with 's', 'es', or 'ies', assume it's already plural
        if (preg_match('/(?:s|es|ies)$/', $basePropertyName)) {
            return $basePropertyName;
        }

        // Use Doctrine Inflector for standard pluralization
        return $this->inflector->pluralize($basePropertyName);
    }

    /**
     * Get the singular form of a property name
     * 
     * @param string $propertyName The property name to singularize
     * @return string The singular form
     */
    public function singularize(string $propertyName): string
    {
        // Check reverse overrides
        $reverseOverrides = array_flip(self::PLURAL_OVERRIDES);
        if (isset($reverseOverrides[$propertyName])) {
            return $reverseOverrides[$propertyName];
        }

        return $this->inflector->singularize($propertyName);
    }


    /**
     * Get the method name for a property (e.g., rolePermissions -> RolePermissions)
     * 
     * @param string $propertyName The property name
     * @return string The method name suffix (e.g., for getRolePermissions)
     */
    public function getMethodNameSuffix(string $propertyName): string
    {
        return ucfirst($propertyName);
    }
}