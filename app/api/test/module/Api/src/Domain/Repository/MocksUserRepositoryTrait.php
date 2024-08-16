<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Mockery\MockInterface;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Domain\Repository\User as UserRepository;

/**
 * Should be used in combination with MocksRepositoriesTrait and MocksServicesTrait
 *
 * @see \Dvsa\OlcsTest\MocksRepositoriesTrait
 * @see \Olcs\TestHelpers\Service\MocksServicesTrait
 */
trait MocksUserRepositoryTrait
{
    /**
     * @var array
     */
    protected $userRepositoryStorage = [];

    /**
     * @return RepositoryServiceManager
     */
    abstract protected function repositoryServiceManager(): RepositoryServiceManager;

    /**
     * @return MockInterface
     */
    abstract protected function setUpMockService(string $class): MockInterface;

    /**
     * @return MockInterface|UserRepository
     */
    protected function userRepository(): MockInterface
    {
        $repositoryServiceManager = $this->repositoryServiceManager();
        if (! $repositoryServiceManager->has('User')) {
            $instance = $this->setUpMockService(UserRepository::class);
            $instance->allows('injectEntity')->andReturnUsing(function (Application $entity) {
                /* @see \Dvsa\OlcsTest\MocksRepositoriesTrait::injectEntities() */
                $this->userRepositoryStorage['id'][$entity->getId()] = $entity;
            });
            $instance->allows('fetchUsingId')->andReturnUsing(function ($command) {
                assert(is_callable([$command, 'getId']));
                return $this->userRepositoryStorage['id'][$command->getId()] ?? null;
            })->byDefault();
            $repositoryServiceManager->setService('User', $instance);
            $repositoryServiceManager->setService('RepositoryFor__' . User::class, $instance);
        }
        return $repositoryServiceManager->get('User');
    }
}
