<?php

namespace Dvsa\Olcs\Api\Domain;

use Dvsa\Olcs\Api\Domain\QueryPartial\QueryPartialInterface;
use Laminas\ServiceManager\AbstractPluginManager;

/**
 * @template-extends AbstractPluginManager<QueryPartialInterface>
 */
class QueryPartialServiceManager extends AbstractPluginManager
{
    protected $instanceOf = QueryPartialInterface::class;
}
