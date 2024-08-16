<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Mockery\MockInterface;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepository;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;
use Dvsa\Olcs\Api\Domain\Repository\ApplicationOperatingCentre as ApplicationOperatingCentreRepository;

trait MocksApplicationOperatingCentreRepositoryTrait
{
    /**
     * @var array
     */
    protected $applicationOperatingCentreRepositoryStorage = [];

    /**
     * @return RepositoryServiceManager
     */
    abstract protected function repositoryServiceManager(): RepositoryServiceManager;

    /**
     * @return MockInterface
     */
    abstract protected function setUpMockService(string $class): MockInterface;

    /**
     * @return MockInterface|LicenceRepository
     */
    protected function applicationOperatingCentreRepository(): MockInterface
    {
        $repositoryServiceManager = $this->repositoryServiceManager();
        if (! $repositoryServiceManager->has('ApplicationOperatingCentre')) {
            $instance = $this->setUpMockService(ApplicationOperatingCentreRepository::class);
            $instance->allows('injectEntity')->andReturnUsing(function (ApplicationOperatingCentre $entity) {
                /* @see \Dvsa\OlcsTest\MocksRepositoriesTrait::injectEntities() */
                assert($entity->getApplication(), 'Expected ApplicationOperatingCentre to have an application with an id');
                $this->applicationOperatingCentreRepositoryStorage['applicationId'][$entity->getApplication()->getId()][] = $entity;
            });
            $instance->allows('fetchByApplicationIdForOperatingCentres')->andReturnUsing(function (int $applicationId) {
                $aocs = $this->applicationOperatingCentreRepositoryStorage['applicationId'][$applicationId] ?? [];
                return array_map(fn(ApplicationOperatingCentre $aoc) => $aoc->serialize(['licence', 'operatingCentre']), $aocs);
            })->byDefault();
            $repositoryServiceManager->setService('ApplicationOperatingCentre', $instance);
            $repositoryServiceManager->setService('RepositoryFor__' . ApplicationOperatingCentre::class, $instance);
        }
        return $repositoryServiceManager->get('ApplicationOperatingCentre');
    }
}
