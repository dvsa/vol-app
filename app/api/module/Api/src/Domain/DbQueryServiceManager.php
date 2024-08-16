<?php

namespace Dvsa\Olcs\Api\Domain;

use Dvsa\Olcs\Api\Domain\Repository\Query\QueryInterface;
use Laminas\ServiceManager\AbstractPluginManager;
use Laminas\ServiceManager\ConfigInterface;

/**
 * @template-extends AbstractPluginManager<QueryInterface>
 */
class DbQueryServiceManager extends AbstractPluginManager
{
    protected $instanceOf = QueryInterface::class;
}
