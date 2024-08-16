<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest;

use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Dvsa\OlcsTest\Builder\BuilderInterface;

trait MocksRepositoriesTrait
{
    /**
     * @return RepositoryServiceManager
     * @deprecated Use repositoryServiceManager
     */
    protected function setUpRepositoryServiceManager(): RepositoryServiceManager
    {
        return $this->repositoryServiceManager();
    }

    /**
     * @return RepositoryServiceManager
     */
    protected function repositoryServiceManager(): RepositoryServiceManager
    {
        assert(is_callable([$this, 'serviceManager']), 'Expected service manager accessor to be defined');
        if (! $this->serviceManager()->has('RepositoryServiceManager')) {
            $instance = new RepositoryServiceManager($this->serviceManager());
            $this->serviceManager()->setService('RepositoryServiceManager', $instance);
        }
        return $this->serviceManager()->get('RepositoryServiceManager');
    }

    protected function injectEntities(object ...$entities): void
    {
        while ($entity = array_pop($entities)) {
            if ($entity instanceof BuilderInterface) {
                $entity = $entity->build();
            }
            $repository = $this->repositoryServiceManager()->get('RepositoryFor__' . $entity::class);
            assert($repository, 'Cannot inject the entity provided: repository is not registered with the repository manager');
            assert(is_callable([$repository, 'injectEntity']), 'Injecting this types of entity is not yet supported. Feel free to add it quickly! :)');
            $repository->injectEntity($entity);
        }
    }
}
