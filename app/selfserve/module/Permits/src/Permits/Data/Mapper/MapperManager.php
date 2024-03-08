<?php

namespace Permits\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\ServiceManager\AbstractPluginManager;

class MapperManager extends AbstractPluginManager
{
    protected $instanceOf = MapperInterface::class;

    /**
     * Validate a plugin
     *
     * Checks that the mapper is an instance of MapperInterface.
     *
     * @param  mixed $plugin
     * @throws \RuntimeException if invalid
     */
    public function validate($plugin)
    {
        if ($plugin instanceof MapperInterface) {
            // we're okay
            return;
        }

        throw new \RuntimeException(sprintf(
            'Mapper must be an instance of MapperInterface; %s given',
            is_object($plugin) ? get_class($plugin) : gettype($plugin)
        ));
    }
}
