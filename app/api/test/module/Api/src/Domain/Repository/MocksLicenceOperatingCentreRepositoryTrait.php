<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\RepositoryServiceManager;
use Mockery\MockInterface;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepository;
use Dvsa\Olcs\Api\Domain\Repository\LicenceOperatingCentre as LicenceOperatingCentreRepository;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;

trait MocksLicenceOperatingCentreRepositoryTrait
{
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
    protected function licenceOperatingCentreRepository(): MockInterface
    {
        $repositoryServiceManager = $this->repositoryServiceManager();
        if (! $repositoryServiceManager->has('LicenceOperatingCentre')) {
            $instance = $this->setUpMockService(LicenceOperatingCentreRepository::class);
            $instance->allows('fetchByLicenceIdForOperatingCentres')->andReturn([])->byDefault();
            $repositoryServiceManager->setService('LicenceOperatingCentre', $instance);
            $repositoryServiceManager->setService('RepositoryFor__' . LicenceOperatingCentreRepository::class, $instance);
        }
        return $repositoryServiceManager->get('LicenceOperatingCentre');
    }
}
