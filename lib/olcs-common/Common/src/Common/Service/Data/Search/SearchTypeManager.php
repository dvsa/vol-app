<?php

namespace Common\Service\Data\Search;

use Common\Data\Object\Search\SearchAbstract;
use Laminas\ServiceManager\AbstractPluginManager;

/**
 * Plugin manager for search data objects
 *
 * Class SearchTypeManager
 * @package Olcs\Service\Data\Search
 * @template-extends AbstractPluginManager<SearchAbstract>
 */
class SearchTypeManager extends AbstractPluginManager
{
    protected $instanceOf = SearchAbstract::class;

    /**
     * Do NOT allow any class which hasn't been explicitly registered to be used as a search type. Changing this to
     * true will probably introduce a security flaw.
     *
     * @var bool
     */
    protected $autoAddInvokableClass = false;

    /**
     * previously we had code that (mis)used a debug method within the Laminas service manager to provide this info,
     * for now we're replicating that behaviour here
     */
    public function getRegisteredServices(): array
    {
        return array_keys($this->factories);
    }
}
