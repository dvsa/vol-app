<?php

namespace Dvsa\Olcs\Api\Domain;

use Dvsa\Olcs\Api\Service\AbstractServiceManagerFactory;

class DbQueryServiceManagerFactory extends AbstractServiceManagerFactory
{
    public const CONFIG_KEY = 'db_query_services';
    public const PLUGIN_MANAGER_CLASS = DbQueryServiceManager::class;
}
