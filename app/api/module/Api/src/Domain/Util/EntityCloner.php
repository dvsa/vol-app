<?php

/**
 * Entity Cloner
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Util;

use Doctrine\Instantiator\Instantiator;

/**
 * Entity Cloner
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class EntityCloner
{
    private static $alwaysIgnore = [
        'id',
        'version',
        'createdOn',
        'createdBy',
        'lastModifiedOn',
        'lastModifiedBy',
        'olbsKey',
    ];

    /**
     * Clones an entity and copies all properties except those in the ignored list
     *
     * @return mixed
     */
    public static function cloneEntity(mixed $sourceEntity, array $ignoredProperties = [])
    {
        $ignoredProperties = array_merge(self::$alwaysIgnore, $ignoredProperties);

        $clone = clone $sourceEntity;

        if (empty($ignoredProperties)) {
            return $clone;
        }

        // Get a template with default properties
        $entityClass = $sourceEntity::class;
        $template = self::instantiate($entityClass);

        // Set ignored properties to their defaults
        foreach ($ignoredProperties as $ignoredProperty) {
            $getter = 'get' . ucfirst((string) $ignoredProperty);
            $setter = 'set' . ucfirst((string) $ignoredProperty);

            // check the setter exists as some properties (eg olbsKey) may get removed in the future
            if (method_exists($clone, $setter)) {
                $clone->$setter($template->$getter());
            }
        }

        unset($template);

        return $clone;
    }

    /**
     * Copies data from the sourceEntity to the targetEntity (can be different type)
     *
     * @return mixed
     */
    public static function cloneEntityInto(mixed $sourceEntity, $targetEntity, array $ignoredProperties = [])
    {
        $ignoredProperties = array_merge(self::$alwaysIgnore, $ignoredProperties);

        if (!is_object($targetEntity)) {
            if ($targetEntity == $sourceEntity::class) {
                return self::cloneEntity($sourceEntity, $ignoredProperties);
            }

            $targetEntity = self::instantiate($targetEntity);
        }

        $sourceData = $sourceEntity->serialize([]);
        $targetData = $targetEntity->serialize([]);

        $matchingProperties = array_intersect_key($sourceData, $targetData);
        $propertiesToCopy = array_diff_key($matchingProperties, array_flip($ignoredProperties));

        foreach ($propertiesToCopy as $name => $value) {
            $getter = 'get' . ucfirst((string) $name);
            $setter = 'set' . ucfirst((string) $name);

            $targetEntity->$setter($sourceEntity->$getter());
        }

        return $targetEntity;
    }

    protected static function instantiate($entityName)
    {
        if (!method_exists($entityName, '__construct')) {
            $object = new $entityName();
        } else {
            $instantiator = new Instantiator();

            $object = $instantiator->instantiate($entityName);

            if (method_exists($object, 'initCollections')) {
                $object->initCollections();
            }
        }

        return $object;
    }
}
