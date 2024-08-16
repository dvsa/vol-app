<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Application\Application;
use Mockery\MockInterface;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepository;
use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;

/**
 * Should be used in combination with MocksRepositoriesTrait and MocksServicesTrait
 *
 * @see \Dvsa\OlcsTest\MocksRepositoriesTrait
 * @see \Olcs\TestHelpers\Service\MocksServicesTrait
 */
trait MocksApplicationRepositoryTrait
{
    /**
     * @var array
     */
    protected $applicationRepositoryStorage = [];

    /**
     * @return RepositoryServiceManager
     */
    abstract protected function repositoryServiceManager(): RepositoryServiceManager;

    /**
     * @return MockInterface
     */
    abstract protected function setUpMockService(string $class): MockInterface;

    /**
     * @return MockInterface|ApplicationRepository
     */
    protected function applicationRepository(): MockInterface
    {
        $repositoryServiceManager = $this->repositoryServiceManager();
        if (! $repositoryServiceManager->has('Application')) {
            $instance = $this->setUpMockService(ApplicationRepository::class);
            $instance->allows('injectEntity')->andReturnUsing(function (Application $entity) {
                /* @see \Dvsa\OlcsTest\MocksRepositoriesTrait::injectEntities() */
                $this->applicationRepositoryStorage['id'][$entity->getId()] = $entity;
            });
            $instance->allows('fetchUsingId')->andReturnUsing(function ($command) {
                assert(is_callable([$command, 'getId']));
                return $this->applicationRepositoryStorage['id'][$command->getId()] ?? null;
            })->byDefault();
            $repositoryServiceManager->setService('Application', $instance);
            $repositoryServiceManager->setService('RepositoryFor__' . Application::class, $instance);
        }
        return $repositoryServiceManager->get('Application');
    }
}
