<?php

namespace Olcs\Service\Data\Search;

use Olcs\Data\Object\Search\SearchAbstract;
use Zend\ServiceManager\AbstractPluginManager;
use Zend\ServiceManager\Exception;

/**
 * Plugin manager for search data objects
 *
 * Class SearchTypeManager
 * @package Olcs\Service\Data\Search
 */
class SearchTypeManager extends AbstractPluginManager
{
    /**
     * Validate the plugin
     *
     * Checks that the filter loaded is either a valid callback or an instance
     * of FilterInterface.
     *
     * @param  mixed $plugin
     * @return void
     * @throws Exception\RuntimeException if invalid
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof SearchAbstract) {
            return;
        }

        throw new Exception\RuntimeException('Invalid class');
    }
}
