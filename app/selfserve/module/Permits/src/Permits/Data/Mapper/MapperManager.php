<?php

namespace Permits\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Laminas\ServiceManager\AbstractPluginManager;

/**
 * @extends AbstractPluginManager<MapperInterface>
 */
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
    #[\Override]
    public function validate($plugin)
    {
        if ($plugin instanceof MapperInterface) {
            // we're okay
            return;
        }

        throw new \RuntimeException(sprintf(
            'Mapper must be an instance of MapperInterface; %s given',
            get_debug_type($plugin)
        ));
    }
}
