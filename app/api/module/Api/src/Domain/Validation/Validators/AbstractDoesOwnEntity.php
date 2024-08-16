<?php

/**
 * Abstract Does Own Entity
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\AuthAwareInterface;
use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\RepositoryManagerAwareInterface;
use Dvsa\Olcs\Api\Domain\RepositoryManagerAwareTrait;

/**
 * Abstract Does Own Entity
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractDoesOwnEntity extends AbstractValidator implements
    AuthAwareInterface,
    RepositoryManagerAwareInterface
{
    use AuthAwareTrait;
    use RepositoryManagerAwareTrait;

    protected $repo;

    public function isValid($entityId)
    {
        $entity = $this->getEntity($entityId);

        return $this->isOwner($entity);
    }

    /**
     * Get the entity to check
     *
     * @param mixed $entityId Entity identifier, mostly the primary key, but not always
     *
     * @return mixed An Entity object
     */
    protected function getEntity(mixed $entityId)
    {
        return $this->getRepo($this->repo)->fetchById($entityId);
    }
}
