<?php

namespace Dvsa\Olcs\Api\Domain;

use Dvsa\Olcs\Api\Service\AbstractServiceManagerFactory;

class QueryPartialServiceManagerFactory extends AbstractServiceManagerFactory
{
    public const CONFIG_KEY = 'query_partial_services';
    public const PLUGIN_MANAGER_CLASS = QueryPartialServiceManager::class;
}
