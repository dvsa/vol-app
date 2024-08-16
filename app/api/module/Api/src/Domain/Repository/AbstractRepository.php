<?php

/**
 * Abstract Repository
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\OptimisticLockException;
use Dvsa\Olcs\Api\Domain\Exception;

/**
 * Abstract Repository
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractRepository extends AbstractReadonlyRepository implements RepositoryInterface
{
    /**
     * Save an entity
     *
     * @param mixed $entity Entity to save
     *
     * @return void
     * @throws Exception\RuntimeException
     * @throws \Exception
     */
    public function save($entity)
    {
        if (!($entity instanceof $this->entity)) {
            throw new Exception\RuntimeException('This repository can only save entities of type ' . $this->entity);
        }

        try {
            $this->getEntityManager()->persist($entity);
            $this->getEntityManager()->flush();
        } catch (OptimisticLockException $e) {
            \Olcs\Logging\Log\Logger::info($e->getMessage() . $entity::class);
            throw $e;
        } catch (\Exception $e) {
            \Olcs\Logging\Log\Logger::crit($e->getMessage());
            throw $e;
        }
    }

    /**
     * Mark an entity for later persisting to the database using flushAll
     *
     * @param mixed $entity Entity to save
     *
     * @return void
     * @throws Exception\RuntimeException
     * @throws \Exception
     */
    public function saveOnFlush(mixed $entity)
    {
        if (!($entity instanceof $this->entity)) {
            throw new Exception\RuntimeException('This repository can only saveOnFlush entities of type ' . $this->entity);
        }

        try {
            $this->getEntityManager()->persist($entity);
        } catch (\Exception $e) {
            \Olcs\Logging\Log\Logger::crit($e->getMessage());
            throw $e;
        }
    }

    /**
     * Persist to the database all entities (of all types) queued using saveOnFlush
     *
     * @throws \Exception
     */
    public function flushAll()
    {
        $this->getEntityManager()->flush();
    }

    /**
     * Clears the EntityManager. All objects that are currently managed
     * by this EntityManager become detached.
     */
    public function clear()
    {
        $this->getEntityManager()->clear();
    }

    /**
     * Delete an entity
     *
     * @param mixed $entity Entity to delete
     *
     * @return void
     * @throws Exception\RuntimeException
     * @throws \Exception
     */
    public function delete($entity)
    {
        if (!($entity instanceof $this->entity)) {
            throw new Exception\RuntimeException('This repository can only delete entities of type ' . $this->entity);
        }

        try {
            $this->getEntityManager()->remove($entity);
            $this->getEntityManager()->flush();
        } catch (\Exception $e) {
            \Olcs\Logging\Log\Logger::crit($e->getMessage());
            throw $e;
        }
    }

    /**
     * Mark an entity for later deleting to the database using flushAll
     *
     * @param mixed $entity Entity to delete
     *
     * @return void
     * @throws Exception\RuntimeException
     * @throws \Exception
     */
    public function deleteOnFlush(mixed $entity)
    {
        if (!($entity instanceof $this->entity)) {
            throw new Exception\RuntimeException(
                'This repository can only deleteOnFlush entities of type ' . $this->entity
            );
        }

        try {
            $this->getEntityManager()->remove($entity);
        } catch (\Exception $e) {
            \Olcs\Logging\Log\Logger::crit($e->getMessage());
            throw $e;
        }
    }
}
