<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Exception;

/**
 * Abstract Versioned Repository
 *
 * Base repository for entities that support versioning.
 * Automatically creates new versions when content changes.
 */
abstract class AbstractVersionedRepository extends AbstractRepository
{
    /**
     * Save entity with automatic versioning
     *
     * @param mixed $entity Entity to save
     * @return void
     * @throws Exception\RuntimeException
     */
    public function save($entity)
    {
        if (!($entity instanceof $this->entity)) {
            throw new Exception\RuntimeException('This repository can only save entities of type ' . $this->entity);
        }

        // Get current version (null if new entity)
        $currentVersion = $entity->getCurrentVersion();
        
        // Extract current state from entity's working properties
        $currentState = $this->extractEntityState($entity);
        
        // Determine if we need a new version
        $needsVersion = false;
        $versionNumber = 1;
        
        if (!$currentVersion) {
            // New entity - always needs first version
            $needsVersion = true;
        } else {
            // Existing entity - check for changes
            if ($this->hasChanges($currentVersion, $currentState)) {
                $needsVersion = true;
                $versionNumber = $currentVersion->getVersionNumber() + 1;
            }
        }
        
        // Create new version if needed
        if ($needsVersion) {
            $versionClass = $this->getVersionEntityClass();
            $newVersion = new $versionClass();
            
            // Set parent relationship
            $parentSetter = 'set' . $this->getEntityShortName();
            if (method_exists($newVersion, $parentSetter)) {
                $newVersion->$parentSetter($entity);
            }
            
            // Set all versioned fields
            foreach ($currentState as $field => $value) {
                $setter = 'set' . ucfirst($field);
                if (method_exists($newVersion, $setter)) {
                    $newVersion->$setter($value);
                }
            }
            
            // Set metadata
            $newVersion->setVersionNumber($versionNumber);
            
            // Set publish date if method exists
            if (method_exists($newVersion, 'setPublishFrom')) {
                $newVersion->setPublishFrom(new \DateTime());
            }
            
            // Update parent's current version
            $entity->setCurrentVersion($newVersion);
            
            // Persist new version (will be saved with parent due to cascade persist)
            $this->getEntityManager()->persist($newVersion);
        }
        
        // Always save parent entity (updates timestamps, etc)
        parent::save($entity);
    }
    
    /**
     * Fetch entity with current version eager loaded
     *
     * @param int $id Entity ID
     * @return mixed
     */
    public function fetchById($id, $hydrateMode = null, $version = null)
    {
        $qb = $this->createQueryBuilder($this->alias);
        
        // Eager load current version
        $qb->select($this->alias, 'cv')
           ->leftJoin($this->alias . '.currentVersion', 'cv')
           ->where($this->alias . '.id = :id')
           ->setParameter('id', $id);
        
        $result = $qb->getQuery()->getResult($hydrateMode);
        
        if (empty($result)) {
            throw new Exception\NotFoundException('Entity not found');
        }
        
        return $result[0];
    }
    
    /**
     * Extract current state from entity
     *
     * @param mixed $entity
     * @return array
     */
    protected function extractEntityState($entity): array
    {
        $state = [];
        foreach ($this->getVersionedFields() as $field) {
            $getter = 'get' . ucfirst($field);
            if (method_exists($entity, $getter)) {
                $state[$field] = $entity->$getter();
            }
        }
        return $state;
    }
    
    /**
     * Check if state has changed from current version
     *
     * @param mixed $currentVersion
     * @param array $newState
     * @return bool
     */
    protected function hasChanges($currentVersion, array $newState): bool
    {
        foreach ($newState as $field => $newValue) {
            $getter = 'get' . ucfirst($field);
            if (!method_exists($currentVersion, $getter)) {
                continue;
            }
            
            $currentValue = $currentVersion->$getter();
            
            // Handle arrays/JSON comparison
            if (is_array($currentValue) || is_array($newValue)) {
                if (json_encode($currentValue) !== json_encode($newValue)) {
                    return true;
                }
            } elseif ($currentValue != $newValue) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get the list of fields that should trigger versioning when changed
     *
     * @return array
     */
    abstract protected function getVersionedFields(): array;
    
    /**
     * Get the version entity class name
     *
     * @return string
     */
    abstract protected function getVersionEntityClass(): string;
    
    /**
     * Get the parent entity short name (for setter method)
     *
     * @return string
     */
    abstract protected function getEntityShortName(): string;
}