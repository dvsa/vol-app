<?php

/**
 * Repository Service Manager Factory
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain;

use Dvsa\Olcs\Api\Domain\Repository\RepositoryInterface;
use Dvsa\Olcs\Api\Service\AbstractServiceManagerFactory;

/**
 * Repository Service Manager Factory
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class RepositoryServiceManagerFactory extends AbstractServiceManagerFactory
{
    public const CONFIG_KEY = 'repository_services';
    public const PLUGIN_MANAGER_CLASS = RepositoryServiceManager::class;
}
