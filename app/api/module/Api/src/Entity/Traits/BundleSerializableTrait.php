<?php

namespace Dvsa\Olcs\Api\Entity\Traits;

use Doctrine\Common\Collections\AbstractLazyCollection;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Persistence\Proxy;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * Bundle Serializable Trait
 */
trait BundleSerializableTrait
{
    /**
     * JSON serialize
     *
     * @return mixed
     * @deprecated
     */
    public function jsonSerialize(): mixed
    {
        $output = [];

        try {
            $vars = get_object_vars($this);
        } catch (EntityNotFoundException) {
            // ORM proxies may reference entities removed by SoftDeleteable
            return null;
        }

        foreach ($vars as $property => $value) {
            $output[$property] = null;

            $isUninitialised = self::isUninitialisedAssociation($value);

            if ($value instanceof Proxy || $isUninitialised) {
                if (!$isUninitialised) {
                    $output[$property] = $value;
                }
                continue;
            }

            if ($value instanceof ArrayCollection) {
                $output[$property] = $value->toArray();
                continue;
            }

            if ($value instanceof AbstractLazyCollection) {
                if ($value->isInitialized()) {
                    $output[$property] = $value->toArray();
                }
                continue;
            }

            $output[$property] = $value;
        }

        return array_merge($output, $this->getCalculatedValues());
    }

    /**
     * Get calculated values
     *
     * @return array
     * @deprecated
     */
    protected function getCalculatedValues()
    {
        return [];
    }

    /**
     * Serialize
     *
     * @param array $bundle Bundle
     *
     * @return array
     */
    public function serialize(array $bundle = [])
    {
        $output = [];

        $excludeProperties = [
            '__initializer__',
            '__cloner__',
            '__isInitialized__',
        ];

        self::recordIdentityDiagnostics($this);

        $vars = get_object_vars($this);

        foreach ($vars as $property => $value) {
            if (in_array($property, $excludeProperties)) {
                continue;
            }

            if (
                $value instanceof Proxy
                || $value instanceof ArrayCollection
                || $value instanceof AbstractLazyCollection
                || $value instanceof BundleSerializableInterface
            ) {
                $propertyBundle = null;

                if (in_array($property, $bundle)) {
                    $propertyBundle = [];
                } elseif (array_key_exists($property, $bundle)) {
                    $propertyBundle = $bundle[$property];
                }

                $value = $this->determineValue($value, $property, $propertyBundle);
            }

            $output[$property] = $value;
        }

        return array_merge($output, $this->getCalculatedBundleValues());
    }

    /**
     * Property bundle is null when we haven't asked for the property
     *
     * @param mixed  $value          Value
     * @param string $property       Property
     * @param array  $propertyBundle Property bundle
     *
     * @return array|null
     */
    private function determineValue(mixed $value, $property, $propertyBundle = null)
    {
        // If we haven't asked for the property
        if ($propertyBundle === null) {
            // ...and it is a RefData entity
            if (
                $value instanceof RefData
                // ...that has already been loaded
                && !self::isUninitialisedAssociation($value)
            ) {
                // ...include it anyway
                return $value->serialize();
            }

            // ...otherwise bail
            return null;
        }

        // If it has not been loaded yet, load it through the getter
        if (self::isUninitialisedAssociation($value)) {
            $value = $this->getPropertyValue($property);
        }

        // If we have an actual entity object
        if ($value instanceof BundleSerializableInterface) {
            // ...then return the serialized entity
            return $this->getSerializedValue($value, $propertyBundle);
        }

        // If we have a collection
        if ($value instanceof Collection) {
            $list = [];

            // Allow criteria mid-bundle
            if (isset($propertyBundle['criteria'])) {
                $value = $value->matching($propertyBundle['criteria']);
            }

            // Allow filter mid-bundle
            if (isset($propertyBundle['filter'])) {
                $value = $value->filter($propertyBundle['filter']);
            }

            // .. serialize each item and add it to the list
            foreach ($value->toArray() as $item) {
                $list[] = $this->getSerializedValue($item, $propertyBundle);
            }

            return $list;
        }

        return null;
    }

    /**
     * Get serialized value
     *
     * @param mixed $value          Value
     * @param array $propertyBundle Property bundle
     *
     * @return mixed|null
     */
    private function getSerializedValue(mixed $value, $propertyBundle)
    {
        if ($value instanceof BundleSerializableInterface) {
            try {
                // try to serialize the value
                return $value->serialize($propertyBundle);
            } catch (EntityNotFoundException) {
                // we may have the object id but will not be able to load it
                // because SoftDeleteable is used
                return null;
            }
        }

        return $value;
    }

    /**
     * Get property value
     *
     * @param string $property Property
     *
     * @return mixed|null
     */
    /**
     * TEMPORARY - VOL-7070 diagnostics for EntityIdentityCollisionException.
     *
     * ORM 3 throws when a second object instance is registered for an identity that
     * is already in the identity map. On reg this fires while serialising a Country,
     * as get_object_vars() below initialises a lazy instance whose identity is
     * already held by a different object. The stack trace names the object being
     * initialised but not the one that got there first, which is what we need.
     *
     * So: remember class+id -> spl_object_id, and log once when a second, distinct
     * object turns up for an identity we have already seen, with a backtrace of the
     * path that produced it. Nothing here initialises anything — the identifier is
     * read through the ClassMetadata accessors that ProxyFactory pre-populates on a
     * lazy ghost — and every failure is swallowed, so diagnostics can never be the
     * thing that breaks a request.
     *
     * Remove once the root cause is found.
     */
    private static array $identityDiagnostics = [];

    private static function recordIdentityDiagnostics(object $entity): void
    {
        try {
            if (!method_exists($entity, 'getId')) {
                return;
            }

            $reflection = new \ReflectionClass($entity);
            if (!$reflection->hasProperty('id')) {
                return;
            }

            $idProperty = $reflection->getProperty('id');
            $id = $idProperty->isInitialized($entity) ? $idProperty->getValue($entity) : null;
            if ($id === null || is_object($id)) {
                return;
            }

            $key = $entity::class . '#' . $id;
            $objectId = spl_object_id($entity);
            $seen = self::$identityDiagnostics[$key] ?? null;

            if ($seen === null) {
                // Queue consumers run for hours; do not let the map grow without bound.
                if (count(self::$identityDiagnostics) > 20000) {
                    self::$identityDiagnostics = [];
                }

                self::$identityDiagnostics[$key] = $objectId;
                return;
            }

            if ($seen === $objectId) {
                return;
            }

            // Second distinct instance for an identity we have already serialised.
            self::$identityDiagnostics[$key] = $objectId;

            \Olcs\Logging\Log\Logger::err(
                'VOL-7070 identity diagnostics: duplicate instance for ' . $key,
                [
                    'data' => [
                        'entity' => $entity::class,
                        'id' => (string) $id,
                        'firstObjectId' => $seen,
                        'secondObjectId' => $objectId,
                        'secondIsUninitialisedLazy' => $reflection->isUninitializedLazyObject($entity),
                        'trace' => array_map(
                            static fn(array $frame): string => ($frame['class'] ?? '')
                                . ($frame['type'] ?? '')
                                . ($frame['function'] ?? '')
                                . ' at ' . basename($frame['file'] ?? '?') . ':' . ($frame['line'] ?? '?'),
                            array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 25), 1)
                        ),
                    ],
                ]
            );
        } catch (\Throwable) {
            // diagnostics must never affect the request
        }
    }

    /**
     * Has this association not been loaded yet?
     *
     * Doctrine hands back two shapes of unloaded association. Under the LazyGhost
     * strategy it is a generated proxy subclass implementing Proxy. Since ORM 3 on
     * PHP 8.4 — which is what this application runs, because symfony/var-exporter 8
     * removed LazyGhost — it is instead a native lazy instance of the entity class
     * itself, which satisfies neither `instanceof Proxy` nor `__isInitialized()`.
     * Both shapes have to be recognised, or unloaded associations get treated as
     * loaded and are silently fetched and serialised.
     */
    private static function isUninitialisedAssociation(mixed $value): bool
    {
        if ($value instanceof Proxy) {
            return !$value->__isInitialized();
        }

        return is_object($value)
            && new \ReflectionClass($value)->isUninitializedLazyObject($value);
    }

    private function getPropertyValue($property)
    {
        $value = null;

        $getter = 'get' . ucfirst($property);

        if (method_exists($this, $getter)) {
            $value = $this->$getter();
        }

        return $value;
    }

    /**
     * Get calculated bundle values
     *
     * @return array
     */
    protected function getCalculatedBundleValues()
    {
        return [];
    }

    /**
     * This method allows our entities to be cast to a string, so we can use "in" criteria with just id's
     * when a collection is initialized
     *
     * @return mixed
     */
    public function __toString()
    {
        if ($this->getId() === null) {
            return '';
        }
        return (string)$this->getId();
    }
}
