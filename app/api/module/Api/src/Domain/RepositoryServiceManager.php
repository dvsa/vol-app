<?php

namespace Dvsa\Olcs\Api\Domain;

use Dvsa\Olcs\Api\Domain\Repository\CompaniesHouseVsOlcsDiffs;
use Dvsa\Olcs\Api\Domain\Repository\DataDvaNi;
use Dvsa\Olcs\Api\Domain\Repository\DataGovUk;
use Dvsa\Olcs\Api\Domain\Repository\ReadonlyRepositoryInterface;
use Dvsa\Olcs\Api\Domain\Repository\RepositoryInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;

/**
 * @template-extends AbstractPluginManager<RepositoryInterface|ReadonlyRepositoryInterface>
 */
class RepositoryServiceManager extends AbstractPluginManager
{
    public const VALIDATE_ERROR = 'Plugin manager "%s" expected an instance of type RepositoryInterface or ReadonlyRepositoryInterface, but "%s" was received';

    private array $exportRepos = [
        DataGovUk::class,
        DataDvaNi::class,
        CompaniesHouseVsOlcsDiffs::class,
    ];

    #[\Override]
    public function validate($instance)
    {
        if ($instance instanceof RepositoryInterface || $instance instanceof ReadonlyRepositoryInterface) {
            return;
        }

        //repos used for data exports have no corresponding Doctrine Entity, and don't implement the usual interfaces
        if (in_array($instance::class, $this->exportRepos)) {
            return;
        }

        throw new InvalidServiceException(sprintf(
            self::VALIDATE_ERROR,
            self::class,
            get_debug_type($instance)
        ));
    }
}
